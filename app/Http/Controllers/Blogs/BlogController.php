<?php

namespace App\Http\Controllers\Blogs;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blogs\DeleteContentRequest;
use App\Http\Requests\Blogs\DeletePostRequest;
use App\Http\Requests\Blogs\SearchPostsRequest;
use App\Http\Requests\Blogs\StoreMarketingPostRequest;
use App\Http\Requests\Blogs\StorePostContentRequest;
use App\Jobs\UploadMarketingPostImage;
use App\Jobs\UploadPostContentImage;
use App\Models\MarketingPost;
use App\Models\PostContent;
use App\Services\BlogService;

class BlogController extends Controller
{

    protected $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;

    }

    /**
     * Encontrar posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPublic(SearchPostsRequest $request)
    {
        try {

            $data = $request->validated();

            $posts = $this->blogService->searchPostsPublic($data);
            
            return ApiResponseHelper::apiSuccess(200, 'Posts obtenidos exitosamente', $posts);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de posts', $e->getMessage(), 500, 'GET_POST_SEARCH_ERROR');
        }
    }

    /**
     * Encontrar posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchManager(SearchPostsRequest $request)
    {
        try {

            $data = $request->validated();

            $posts = $this->blogService->searchPostsManager($data);
            
            return ApiResponseHelper::apiSuccess(200, 'Posts obtenidos exitosamente', $posts);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de posts', $e->getMessage(), 500, 'GET_POST_SEARCH_ERROR');
        }
    }

    /**
     * Crear post.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreMarketingPostRequest $request )
    {
        try {

            $data = $request->validated();

            $url_name = $data['title']; 

            $words = explode(' ', $url_name, 9);
            array_pop($words);

            $url_name = strtolower(implode('-', $words)); 
            $url_name = preg_replace('/[^a-z0-9-]/', '', $url_name);

            $post = MarketingPost::create([
                'title' => $data['title'],
                'url_name' => $url_name
            ]);

            $image = $request->file('image');

            $path = \App\Support\UploadableImage::storeTemp($image);

            UploadMarketingPostImage::dispatchSync($path, $post, $image->getClientOriginalName());

            return ApiResponseHelper::apiSuccess(200, 'Posts creado exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el post', $e->getMessage(), 500, 'CREATE_POST_ERROR');
        }
    } 

    /**
     * Crear contenido para un post.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createContent(StorePostContentRequest $request )
    {
        try {

            $data = $request->validated();

            $post = MarketingPost::findByUuid($data['post_uuid']);
            
            $content = PostContent::create([
                'type_content' => $data['type_content'],
                'url_name' => $data['content_text'],
                'post_id' => $post->id
            ]);
            
            if (isset($data['content_multimedia_1']) && $request->hasFile('content_multimedia_1')) {

                $image = $request->file('content_multimedia_1');
                $path = \App\Support\UploadableImage::storeTemp($image);

                UploadPostContentImage::dispatch($path, $content, $image->getClientOriginalName(), 'content_multimedia_1');

            }

            if (isset($data['content_multimedia_2']) && $request->hasFile('content_multimedia_2')) {

                $image = $request->file('content_multimedia_2');
                $path = \App\Support\UploadableImage::storeTemp($image);

                UploadPostContentImage::dispatch($path, $content, $image->getClientOriginalName(), 'content_multimedia_2');

            }

            return ApiResponseHelper::apiSuccess(200, 'Contenido creado exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el contenido', $e->getMessage(), 500, 'CREATE_POST_CONTENT_ERROR');
        }
    } 

    /**
     * Eliminar post mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeletePostRequest $request)
    {
        try {

            $data = $request->validated();

            $post = MarketingPost::findByUuid($data['uuid']);

            if ($post) {
                
                $post->delete();

                return ApiResponseHelper::apiSuccess(200, 'Post eliminado exitosamente');

            } else {
                return ApiResponseHelper::apiError('La post no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_POST_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el post', $e->getMessage(), 500, 'GET_POST_ERROR');
        }
    }

    /**
     * Eliminar contenido de post mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteContent(DeleteContentRequest $request)
    {
        try {

            $data = $request->validated();

            $content = PostContent::findByUuid($data['content_uuid']);

            if ($content) {
                
                $content->delete();

                return ApiResponseHelper::apiSuccess(200, 'Contenido eliminado exitosamente');

            } else {
                return ApiResponseHelper::apiError('La contenido no existe', 'No existe el id: '. $data['content_uuid'] ,404, 'GET_POST_CONTENT_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el contenido', $e->getMessage(), 500, 'GET_CONTENT_POST_ERROR');
        }
    }

}
