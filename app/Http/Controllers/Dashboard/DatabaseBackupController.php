<?php

namespace App\Http\Controllers\Dashboard;

use File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use ZipArchive;

class DatabaseBackupController extends Controller
{    public function index()
    {
        File::ensureDirectoryExists(storage_path('/app/POS'));

        return view('database.index', [
            'files' => File::allFiles(storage_path('/app/POS'))
        ]);
    }

    // Backup database is not working, and you need to enter manually in terminal with command php artisan backup:run.
    public function create(){
        \Artisan::call('backup:run');

        return Redirect::route('backup.index')->with('success', 'Database Backup Successfully!');
    }

    public function download(String $getFileName)
    {
        $path = storage_path('app\POS/' . $getFileName);

        return response()->download($path);
    }

    public function delete(String $getFileName)
    {
        Storage::delete('POS/' . $getFileName);

        return Redirect::route('backup.index')->with('success', 'Database Deleted Successfully!');
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
}
