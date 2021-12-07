<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

class PostController extends AbstractController
{
    /**
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="order",
     *     in="query",
     *     description="The field used to order rewards",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="rewards")
     * @Security(name="Bearer")
     */
    #[Route('/post/getAllPostByUser/{id}', name: 'get_all_post_by_user', methods: ['GET'])]
    public function getAllPostByUser($id, PostRepository $postRepository, UserRepository $userRepository): Response
    {
        try {
            $user = $userRepository->find($id);
            if(!$user){
                $data = [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errors' => "User not found",
                ];
                return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $posts = $postRepository->findBy(["user" => $user]);
            $postMas = array();
            foreach ($posts as $post){
                $postSerialize = [
                    "body" => $post->getBody(),
                    "head" => $post->getHead()
                ];
                array_push($postMas, $postSerialize);
            }

            return $this->response($postMas);
        }catch (Exception){
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ];
            return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function response($data, $status = Response::HTTP_OK, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}
