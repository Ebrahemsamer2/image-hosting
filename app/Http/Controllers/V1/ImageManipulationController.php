<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\V1\Controller;
use App\Models\ImageManipulation;
use App\Http\Requests\ResizeImageRequest;
use Illuminate\Support\Facades\File;

use App\Models\Album;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function byAlbum(Album $album)
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImageManipulationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();

        $image = $all['image'];
        unset( $all['image'] );

        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => null,
        ];

        if( isset( $all['album_id'] ) ) {
            $data['album_id'] = $all['album_id'];
        }

        $dir = "images/" . \Str::random() . '/';
        $absolute_path = public_path($dir);
        File::makeDirectory($absolute_path);

        if( $image instanceof UploadFile )
        {
            $extension = $image->getClientOriginalExtension();
            $data['name'] = $image->getClientOriginalName();
            $filename = pathinfo($name, PATHINFO_FILENAME);
            $path = $image->move( $absolute_path, $data['name']);
        }
        else 
        {
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            copy($image, $absolute_path . $data['name']);
        }

        $original_path = $absolute_path . $data['name'];
        $data['path'] = $dir . $data['name'];

        $w = $all['w'];
        $h = $all['h']??false;

        list('width', 'height', 'image') = $this->getOriginalWidthAndHeight($w, $h, $original_path);

        $resized_filename = $filename . '-resized' . $extension;
        $image->resize($width, $height)->save($absolute_path . $resized_filename);

        $data['output_path'] = $dir . $resized_filename;

        $image_manipulation = ImageManipulation::create($data);
        return $image_manipulation;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $imageManipulation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $imageManipulation)
    {
        $imageManipulation->delete();
        return resource('Image has been deleted!', 204);
    }

    private function getOriginalWidthAndHeight($w, $h, $original_path)
    {
        $image = Image::make($original_path);
        
        $original_width = $image->width();
        $original_height = $image->height();

        if( str_ends_with($w, "%") )
        {
            $ratio_w = (float)str_replace('%', '', $w);
            $ratio_h = $h ? (float)str_replace('%', '', $h) : $ratio_w;

            $new_width = ( $ratio_w * $original_width ) / 100;
            $new_height = ( $ratio_h * $original_height ) / 100;
        }
        else 
        {
            $new_width = (float)$w;
            $new_height = $h ? (float)$h : $original_height * ( $new_width / $original_width );
        }

        return [$new_width, $new_height, $image];
    }
}
