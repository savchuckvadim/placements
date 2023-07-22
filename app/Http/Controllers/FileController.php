<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public static function processFields(Request $request)
    {
        // Проверяем наличие поля fields в запросе
        if (!$request->has('fields')) {
            return response()->json(['status' => 'error', 'message' => 'Поле fields отсутствует'], 400);
        }

        $fields = $request->input('fields');

        // Проверяем, является ли $fields массивом
        if (!is_array($fields)) {
            return response()->json(['status' => 'error', 'message' => 'Поле fields должно быть массивом'], 400);
        }

        // Путь к исходному и результирующему файлам
        $templatePath = storage_path('app/template.docx');
        $resultPath = storage_path('app/result.docx');

        // Проверяем, существует ли исходный файл
        if (!file_exists($templatePath)) {
            return response()->json(['status' => 'error', 'message' => 'Исходный файл не найден'], 400);
        }

        // try {
        //     $template = new TemplateProcessor($templatePath);

        //     // Заменяем каждое вхождение field->name на field->bitrixId
        //     foreach ($fields as $field) {
        //         if (!isset($field->name) || !isset($field->bitrixId)) {
        //             return response()->json(['status' => 'error', 'message' => 'Один из объектов fields не содержит поля name или bitrixId'], 400);
        //         }
                
        //         $template->setValue($field->name, $field->bitrixId);
        //     }

        //     // Сохраняем результат
        //     $template->saveAs($resultPath);
        // } catch (Exception $e) {
        //     // Обрабатываем возможные исключения
        //     return response()->json(['status' => 'error', 'message' => 'Ошибка обработки шаблона: ' . $e->getMessage()], 500);
        // }

        // Возвращаем успешный ответ
        return response()->json(['status' => 'success', 'message' => 'Обработка прошла успешно'], 200);
    }
}
