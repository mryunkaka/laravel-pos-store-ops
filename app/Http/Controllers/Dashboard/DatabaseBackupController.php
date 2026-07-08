<?php

namespace App\Http\Controllers\Dashboard;

use File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use ZipArchive;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        File::ensureDirectoryExists($this->backupDirectory());

        return view('database.index', [
            'files' => File::allFiles($this->backupDirectory())
        ]);
    }

    public function create()
    {
        try {
            Artisan::call('backup:run', ['--disable-notifications' => true]);
        } catch (\Throwable $exception) {
            return Redirect::route('backup.index')->with('error', 'Backup gagal: ' . $exception->getMessage());
        }

        return Redirect::route('backup.index')->with('success', 'Backup database berhasil dibuat.');
    }

    public function download(String $getFileName)
    {
        $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . $getFileName;

        return response()->download($path);
    }

    public function delete(String $getFileName)
    {
        Storage::delete($this->backupFolderName() . '/' . $getFileName);

        return Redirect::route('backup.index')->with('success', 'Backup database berhasil dihapus.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt,zip|max:51200',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $sqlPath = null;
        $tempDirectory = storage_path('app/restore-' . now()->format('YmdHis'));

        if ($extension === 'zip') {
            File::ensureDirectoryExists($tempDirectory);
            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) !== true) {
                return Redirect::route('backup.index')->with('error', 'File backup zip tidak bisa dibuka.');
            }

            $zip->extractTo($tempDirectory);
            $zip->close();

            $sqlFiles = File::allFiles($tempDirectory);
            foreach ($sqlFiles as $sqlFile) {
                if (strtolower($sqlFile->getExtension()) === 'sql') {
                    $sqlPath = $sqlFile->getRealPath();
                    break;
                }
            }
        } else {
            $sqlPath = $file->getRealPath();
        }

        if (!$sqlPath || !File::exists($sqlPath)) {
            File::deleteDirectory($tempDirectory);
            return Redirect::route('backup.index')->with('error', 'File SQL tidak ditemukan di backup.');
        }

        DB::unprepared(File::get($sqlPath));
        File::deleteDirectory($tempDirectory);

        return Redirect::route('backup.index')->with('success', 'Database berhasil dipulihkan dari backup.');
    }

    private function backupFolderName(): string
    {
        return config('backup.backup.name', config('app.name', 'Laravel'));
    }

    private function backupDirectory(): string
    {
        return storage_path('app/' . $this->backupFolderName());
    }
}
