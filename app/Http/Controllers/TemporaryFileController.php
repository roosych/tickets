<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemporaryFileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'],
            'folder' => ['required', 'string'],
        ]);

        if ($request->hasFile('media')) {
            $image = $request->file('media');
            $folder = $request->input('folder');
            $filename = $image->getClientOriginalName();

            $path = 'uploads/tmp/' . $folder;
            $image->storeAs($path, $filename, 'public');

            return response()->json([
                'folder' => $folder, // UUID папки
                'filename' => $filename,
                'path' => $folder . '/' . $filename,
            ]);
        }
        return response()->json(['folder' => '']);
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Если пришла строка, а не массив — приведём к массиву
        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $filePath) {
            if (!$filePath) {
                continue;
            }

            // Формируем путь к файлу в storage
            $fullPath = 'uploads/tmp/' . $filePath;

            if (Storage::disk('public')->exists($fullPath)) {
                Storage::disk('public')->delete($fullPath);
            }
        }

        return response()->noContent();
    }

    public function deleteTempFolder(Request $request)
    {
        $folder = $request->input('folder');

        if ($folder) {
            $folderPath = 'uploads/tmp/' . $folder;
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->deleteDirectory($folderPath);
            }
        }

        return response()->noContent();
    }
}
