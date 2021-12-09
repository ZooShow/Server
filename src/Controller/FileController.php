<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use App\Repository\FileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RespondService;
use Exception;

#[Route('/file', name: 'file_')]
class FileController extends AbstractController
{
    private RespondService $respondService;

    public function __construct(RespondService $respondService){
        $this->respondService = $respondService;
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route(name: 'post', methods: ['POST'])]
    public function uploadFile(Request $request, UserRepository $userRepository):Response
    {
        $user = $this->getUser();
        try {
            $fileRequest = $request->files->get('file');
            $fileDirectory = $this->getParameter('kernel.project_dir') . '/src/FileStorage';
            $fileName = md5(uniqid()).".".$fileRequest->guessExtension();
            $file = new File();
            $file->setFileName($fileName);
            $file->setUser($userRepository->findOneBy(['login'=>$user->getUserIdentifier()]));
            $file->setOriginalName($fileRequest->getClientOriginalName());
            $fileRequest->move($fileDirectory, $fileName);
            $file->setFileSize(filesize($fileDirectory . "/" . $fileName));
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();
            $data = [
                "status"=>Response::HTTP_OK,
                "success"=>"File uploaded successfully"
            ];
            return $this->respondService->response($data);
        } catch (Exception){
            $data = [
                "status"=>Response::HTTP_UNPROCESSABLE_ENTITY,
                "errors"=>"Data no valid"
            ];
            return $this->respondService->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param FileRepository $fileRepository
     * @param $name
     * @return Response
     */
    #[Route('/{name}', name: 'get_by_name', methods: ['GET'])]
    public function downloadFile(FileRepository $fileRepository, $name):Response
    {
        try {
            $file = $fileRepository->findOneBy(['fileName'=>$name]);
            $fileOnServerName = $this->getParameter('kernel.project_dir') . '/src/FileStorage/' . $name;
            $response = new BinaryFileResponse($fileOnServerName);
            $response->headers->set('Content-Type', 'text/plain');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getOriginalName());
            return $response;
        } catch (Exception){
            $data = [
                "status"=>Response::HTTP_UNPROCESSABLE_ENTITY,
                "errors"=>"Data no valid"
            ];
            return $this->respondService->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route(name: 'get_list', methods: ['GET'])]
    public function getListFiles(FileRepository $fileRepository, UserRepository $userRepository):Response
    {
        try {
            $user = $userRepository->findOneBy(['login'=>$this->getUser()->getUserIdentifier()]);
            $files = $fileRepository->findBy(["user"=>$user]);
            $data = [];
            foreach ($files as $file){
                $data[] = [
                    "name" => $file->getFileName(),
                    "original_name" => $file->getOriginalName(),
                    "size" => $file->getFileSize()
                ];
            }
            return $this->respondService->response($data);
        } catch (Exception $e){
//            dd($e);
            $data = [
                "status"=>Response::HTTP_UNPROCESSABLE_ENTITY,
                "errors"=>"Data no valid"
            ];
            return $this->respondService->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/{name}', name: 'delete', methods: ['DELETE'])]
    public function deleteByName($name, FileRepository $fileRepository, EntityManagerInterface $em):Response
    {
        $file = $fileRepository->findOneBy(['fileName'=>$name]);
        if (!$file){
            $data = [
                "status"=>Response::HTTP_UNPROCESSABLE_ENTITY,
                "errors"=>"Data no valid"
            ];
            return $this->respondService->response($data, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $em->remove($file);
        $em->flush();
        unlink($this->getParameter('kernel.project_dir') . '/src/FileStorage/' . $name);
        $data = [
            "status"=>Response::HTTP_OK,
            "success"=>"File deleted successfully"
        ];
        return $this->respondService->response($data);
    }
}
