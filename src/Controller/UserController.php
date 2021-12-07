<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Post;
use App\Repository\PostRepository;


class UserController extends AbstractController
{
    #[Route('/user/add', name: 'user_add', methods: ['POST'])]
    public function addUser(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        try{
            $request = $this->transformJsonBody($request);
            $user = new User();
            $user->setName($request->get('name'));
            $user->setLogin($request->get('login'));
            $user->setPassword($passwordEncoder->hashPassword(
                $user,
                $request->get('password')
            ));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $data = [
                "status"=>Response::HTTP_OK,
                "success"=>"User added successfully"
            ];
            return $this->response($data);
        } catch (Exception){
            $data = [
                "status"=>Response::HTTP_UNPROCESSABLE_ENTITY,
                "errors"=>"Data no valid"
            ];
            return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/user/post/{id}', name:'add_post', methods: ['POST'])]
    public function addPost(Request $request, $id):JsonResponse{
        try{
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getReference(User::class, $id);

            $post = new Post();
            $post->setHead($request->get('head'));
            $post->setBody($request->get('body'));
            $post->setUser($user);
            $entityManager->persist($post);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "Post added successfully",
            ];
            return $this->response($data);
        } catch (Exception){
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ];
            return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    #[Route('/user/post/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function deletePost($id, PostRepository $postRepository):JsonResponse{
        $post = $postRepository->find($id);
        if (!$post){
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Post not found",
            ];
            return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "Post deleted successfully",
        ];
        return $this->response($data);
    }

    #[Route('/user/post/{id}', name: 'update_post', methods: ['PUT'])]
    public function updatePost($id, PostRepository $postRepository, Request $request):JsonResponse{
        $post = $postRepository->find($id);
        if (!$post){
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Post not found",
            ];
            return $this->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request = $this->transformJsonBody($request);

        $post->setHead($request->get('head'));
        $post->setBody($request->get('body'));

        $em = $this->getDoctrine()->getManager();
//        $em->remove($post);
        $em->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "Post updated successfully",
        ];
        return $this->response($data);
    }


    public function response($data, $status = Response::HTTP_OK, $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $request;
        }
        $request->request->replace($data);
        return $request;
    }
}
