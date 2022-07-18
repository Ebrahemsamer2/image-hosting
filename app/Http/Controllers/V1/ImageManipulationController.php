<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ImageManipulation;
use App\Http\Requests\ResizeImageRequest;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

use App\Models\Album;

use App\Http\Resources\V1\ImageManipulationResource;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ImageMainpulationResource::collection( ImageManipulation::paginate() );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function byAlbum(Album $album)
    {
        return ImageMainpulationResource::collection( ImageManipulation::where('album_id', $album)->paginate() );
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
            'user_id' => 1,
        ];
        
        $data['album_id'] = 1;

        if( isset( $all['album_id'] ) ) {
            $data['album_id'] = $all['album_id'];
        }

        $dir = "images/" . \Str::random() . '/';
        $absolute_path = public_path($dir);
        File::makeDirectory($absolute_path, 0755, true);
        
        if( $image instanceof UploadFile )
        {
            $data['name'] = $image->getClientOriginalName();
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $original_path = $absolute_path . $data['name'];
            $data['path'] = $dir . $data['name'];
            $image->move( $original_path, $data['name']);
        }
        else 
        {
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);

            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $original_path = $absolute_path . $data['name'];
            copy($image, $original_path);
            $data['path'] = $dir . $data['name'];
        }

        $w = $all['w'];
        $h = $all['h']??false;

        $original_width_height = $this->getOriginalWidthAndHeight($w, $h, $original_path);
        $width = $original_width_height[0];
        $height = $original_width_height[1];
        $image = $original_width_height[2];

        $resized_filename = $filename . '-resized.' . $extension;
        $image->resize($width, $height)->save($absolute_path . $resized_filename);

        $data['output_path'] = $dir . $resized_filename;

        $image_manipulation = ImageManipulation::create($data);
        return new ImageManipulationResource($image_manipulation);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $image)
    {
        return new ImageManipulationResource( $image );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $image)
    {
        $image->delete();
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
