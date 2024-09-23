<?php

namespace App\Http\Controllers;

use App\Models\TemporaryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemporaryFileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'media' => ['file','mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx','max:10240'],
        ]);

        if($request->hasFile('media')) {
            $image = $request->file('media');
            $filename = $image->getClientOriginalName();
            //$folder = uniqid() . time();
            $folder = time() . mt_rand(1000, 9999);

            // Получение размера файла в байтах
            $size = $image->getSize();
            $extension = $image->getClientOriginalExtension();
            $uniqueFilename = uniqid() . '_' . time() . '.' . $extension;

            // Путь для сохранения файла
            $path = 'uploads/tmp/' . $folder;
            // Сохраняем файл в папку
            $image->storeAs($path, $filename, 'public');

            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $filename,
                'unique_filename' => $uniqueFilename,
                'size' => $size,
                'extension' => $extension,
            ]);

            //return $folder;
            session(['uploaded_folder' => $folder]); // чтобы получить в методе delete()
            return response()->json(['folder' => $folder]);
        }
        //return '';
        return response()->json(['folder' => '']);
    }

    public function delete()
    {
        // Получаем данные из запроса и декодируем их из строки JSON
        $jsonString = request()->getContent(); // Получаем содержимое запроса как строку
        $foldersArray = json_decode($jsonString, true); // Преобразуем строку JSON в массив

        // Проверяем, является ли результат строкой, представляющей JSON-массив
        if (is_string($foldersArray)) {
            // Убираем кавычки с начала и конца строки
            $foldersArray = trim($foldersArray, '"');
            // Декодируем строку снова как JSON
            $foldersArray = json_decode($foldersArray, true);
        }

        if (!is_array($foldersArray)) {
            $foldersArray = [];
        }

        // Проверка если в сессии есть сохраненная имя папки
        if (session()->has('uploaded_folder')) {
            $foldersArray[] = session('uploaded_folder');
            session()->forget('uploaded_folder');
        }

        foreach ($foldersArray as $folder) {
            $tempFile = TemporaryFile::where('folder', $folder)->first(); //request()->getContent()
            if($tempFile) {
                Storage::disk('public')->deleteDirectory('uploads/tmp/' . $tempFile->folder);
                $tempFile->delete();
            }
        }
        //dd($foldersArray);

        return response()->noContent();
    }
}
