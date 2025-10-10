<?php

namespace App\Services;

use App\Models\MarketingPost;

class BlogService
{   
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Busca posts en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Posts encontrados.
     */
    public function searchPostsPublic($data)
    {
        // Crear la consulta base
        $posts = MarketingPost::where('status', '!=', 'unpublished')->paginate($data['paginate']);

        return $posts;
    }


    /**
     * Busca posts en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Posts encontrados.
     */
    public function searchPostsManager($data)
    {
        // Crear la consulta base
        $posts = MarketingPost::paginate($data['paginate']);

        return $posts;
    }


}
