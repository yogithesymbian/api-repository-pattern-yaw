<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use URL;
use App\User;

class ImageController extends Controller
{
    public function fileUpload(Request $request) {

        $name = $request->input('name');

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $name = $image->getClientOriginalName();
            $destinationPath = storage_path('/app');
            $image->move($destinationPath, $name);
            // $url = '/storage';
            // $url_sub = '/public';
            // $url = URL::to('/storage');
            // $url_sub = "http://kulsummarssy.com/gambar";
            // $url_sub = "http://47.254.248.35/api/api-book-audio/public/storage";
            // $url_sub = "http://y.id:8000/storage";

            return response()->json([
                'success' => true,
                'message'=>"image is uploaded",
                'user_photo' => $name
                // 'root_url_img' => $url."/".$name,
                // 'url_img' => $url_sub."/".$name
            ], 201);
            }else {
                return response()->json([
                    'success' => false,
                    'message'=>"uploaded is error"
                ], 201);
            }
    }

    public function delFoto(Request $request) {

        $id = $request->input('id');

        $file = User::where('id', $id)->first();

        $file_path = $file->user_photo;

        Storage::delete($file_path);

    }
}
