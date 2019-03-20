<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Common\Services\CommonServiceFactory;
use Carbon\Carbon;

class MediaController extends CommonController
{
    public function index(Request $request)
    {

    }

    /**
     * @SWG\POST(
     *      path="/media/upload",
     *      operationId="postPetsFile",
     *      tags={"Media"},
     *      summary="Upload file",
     *      description="Returns file detail",
     *      @SWG\Parameter(
     *         description="file to upload",
     *         in="formData",
     *         name="files[]",
     *         required=false,
     *         type="file"
     *     ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request"),
     *       security={
     *           {"api_key_security_example": {}}
     *       }
     *     )
     *
     * Returns list of pets
     */
    public function upload(Request $request)
    {
        $input_data = $request->all();
        $validator = Validator::make(
            $input_data, [
            'files.*' => 'required|mimes:jpg,jpeg,png,bmp|max:20000'
        ], [
                'files.*.required' => 'Please upload an image',
                'files.*.mimes' => 'Only jpeg,png and bmp images are allowed',
                'files.*.max' => 'Sorry! Maximum allowed size for an image is 20MB',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors()->all());
        }

        $files = $request->file('files');
        try {
            $arrReturn = array();
            if (is_object($files)) {
                $arrReturn[] = self::_upload($files);
            } else {
                foreach ($files as $file) {
                    $arrReturn[] = self::_upload($file);
                }
            }
            return $this->sendResponse($arrReturn, 'Upload successfully.');
        } catch (Exception $e) {
            return $this->sendError('Upload Error.', $e->getMessage());
        }
    }

    private function _upload($file)
    {
        try {
            $fileName = time() . '.' . $file->getClientOriginalName();
            $dir = 'upload' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
            \Storage::disk('public')->putFileAs($dir, $file, $fileName);
            $url = config('app.url') . '/storage/' . urlencode(str_replace(DIRECTORY_SEPARATOR, '/', $dir . DIRECTORY_SEPARATOR . $fileName));
            $input = array(
                'dir' => str_replace(DIRECTORY_SEPARATOR, '/', $dir),
                'name' => $fileName,
                'url' => $url,
                'file_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            );
            $upload = CommonServiceFactory::mMediaService()->create($input);
            return ['id' => $upload['id'], 'url' => $url];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @SWG\DELETE(
     *      path="/media/delete",
     *      operationId="postPetsFile",
     *      tags={"Media"},
     *      summary="Delete file",
     *      description="Delete file",
     *      @SWG\Parameter(
     *         description="file to upload",
     *         in="formData",
     *         name="files[]",
     *         required=false,
     *         type="file"
     *     ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request"),
     *       security={
     *           {"api_key_security_example": {}}
     *       }
     *     )
     *
     * Delete file
     */
    public function delete(Request $request)
    {

    }
}
