<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    public function index() {
        $images = Image::all();
        return view('index', ['images' => $images]);
    }

    public function upload(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255|min:6',
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
                Rule::dimensions()->maxWidth(2000)->maxHeight(2000)
            ]

        ]);

        if($request->hasFile('image')){
            $title = $request->only('title');
            $image = $request->file('image');
            $name = $image->hashName();
            $return = $image->storePublicly('uploads', 'public', $name);
            $url = asset('storage/'.$return);
            try {
                Image::create([
                'title' => $title['title'],
                'url' => $url
            ]);}catch (Exception $error) {
                Storage::disk('public')->delete($return);
                return redirect()->back()->withErrors([
                    'error' => 'Erro ao salvar a imagem. Tente novamente.'
                ]);
            }

        }

        return redirect()->route('index');
    }

    public function delete($id) {
        $image = Image::findOrFail($id);
        $url = parse_url($image->url);
        $path = ltrim($url['path'], "/storage\/");
        if(Storage::disk('public')->exists($path)){
            Storage::disk('public')->delete($path);
            $image->delete();
        }

        return redirect()->route('index');
    }
}