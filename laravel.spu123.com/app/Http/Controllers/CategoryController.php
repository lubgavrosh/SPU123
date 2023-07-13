<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Validator;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
    /**
     * Отримати список всіх категорій.
     *
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category",
     *     @OA\Response(response="200", description="Список категорій.")
     * )
     */
    public function index()
    {
        $list = Category::all();
        return response()->json($list, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Отримати категорію за її ID.
     *
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/category/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(response="200", description="Список категорій."),
     *     @OA\Response(
     *        response=404,
     *        description="Неправильний ID",
     *        @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="Вибачте, було вказано неправильний ID категорії. Спробуйте інший.")
     *        )
     *     )
     * )
     */
    public function getById($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Зберегти нову категорію.
     *
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","image","description"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Додати категорію.")
     * )
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $message = array(
            'name.required' => "Вкажіть назву категорії",
            'image.required' => "Вкажіть фото категорії",
            'description.required' => "Вкажіть опис категорії",
        );
        $validator = Validator::make($input, [
            'name' => 'required',
            'image' => 'required',
            'description' => 'required'
        ], $message);

        // Перевірка наявності помилок у вхідних даних
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Згенерувати унікальне ім'я файлу
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $sizes = [50, 150, 300, 600, 1200];

            foreach ($sizes as $size) {
                $fileSave = $size . '_' . $filename;
                // Змінити розмір зображення зі збереженням пропорцій
                $resizedImage = Image::make($image)->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode();
                // Зберегти зменшене зображення
                $path = public_path('uploads/' . $fileSave);
                file_put_contents($path, $resizedImage);
            }
            $input['image'] = $filename;
        }

        // Створити нову категорію з вхідними даними
        $category = Category::create($input);
        return response()->json($category, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Оновити категорію за її ID.
     *
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/category/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Додати категорію.")
     * )
     */
    public function update($id, Request $request)
    {
        $category = Category::findOrFail($id);
        $input = $request->all();
        $message = array(
            'name.required' => "Вкажіть назву категорії",
            'description.required' => "Вкажіть опис категорії",
        );
        $validator = Validator::make($input, [
            'name' => 'required',
            'description' => 'required'
        ], $message);

        // Перевірка наявності помилок у вхідних даних
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Згенерувати унікальне ім'я файлу
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $sizes = [50, 150, 300, 600, 1200];

            // Видалення старих зображень
            foreach ($sizes as $size) {
                $fileDelete = $size . '_' . $category->image;
                $removePath = public_path('uploads/' . $fileDelete);
                if (file_exists($removePath)) {
                    unlink($removePath);
                }
            }

            foreach ($sizes as $size) {
                $fileSave = $size . '_' . $filename;
                // Зміна розміру зображення зі збереженням пропорцій
                $resizedImage = Image::make($image)->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode();
                // Збереження зменшеного зображення
                $path = public_path('uploads/' . $fileSave);
                file_put_contents($path, $resizedImage);
            }
            $input['image'] = $filename;
        } else {
            // Якщо зображення не було завантажене, зберігаємо оригінальне зображення
            $input['image'] = $category->image;
        }

        // Оновлення категорії з оновленими даними
        $category->update($input);

        // Повернення оновленої категорії у форматі JSON
        return response()->json($category, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Видалити категорію за її ID.
     *
     * @OA\Delete(
     *     path="/api/category/{id}",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішне видалення категорії"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категорії не знайдено"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизований"
     *     )
     * )
     */
    public function delete(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $sizes = [50, 150, 300, 600, 1200];
        // Видалення старих зображень
        foreach ($sizes as $size) {
            $fileDelete = $size . '_' . $category->image;
            $removePath = public_path('uploads/' . $fileDelete);
            if (file_exists($removePath)) {
                unlink($removePath);
            }
        }
        $category->delete();
        return 204;
    }
}
