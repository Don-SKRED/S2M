<?php
namespace App\Controller;

use App\Entity\Upload;
use App\Entity\User;

use App\Repository\CourrierRepository;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Form\UploadType;
use App\Entity\Courrier;
use App\Form\CourrierType;
use Container3OQpxVD\getCourrierRepositoryService;
use PDO;
use PDOException;
use MercurySeries\FlashyBundle\FlashyNotifier;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/page")
 * Class PageController
 * @package App\controller
 */
class pageController extends AbstractController
{
    /**
     * @Route("/home/{id}", name="page_index", methods={"GET","POST"})
     */
    public function show(Request $request, FlashyNotifier $flashy, CourrierRepository $CourrierRepository): Response
   {
       try{
           $pdo=new PDO("mysql:host=localhost;dbname=stage2","root","");

       }
       catch(PDOException $e){
           echo $e->getMessage();
       }
       /*
*/
       $courrier = new Courrier();
       $user = $this->getUser();
       $form = $this->createForm(CourrierType::class,$courrier);
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid())
       {
           $file = $courrier->getFichier();
           $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
           $typemimes = $file->getClientMimeType();
           // this is needed to safely include the file name as part of the URL
           $newFilename = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

           // envoye du fichier sur la base de données
           //  $filename = $file->getClientOriginalName();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename);
           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename);

           //prendre l'envoyeur
           $courrier->setSender($this->getUser());
           $em =$this->getDoctrine()->getManager();
           $em->persist($courrier);
           $em->flush();
           //mercuryflash
        $flashy->success('Courrier envoyé avec succès!');
        
           $id_courrier = $courrier->getId();

           //si le fichier existe
           $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
           $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

           $spreadsheet = $reader->load($UploadedFile->getPathname());
           $data = $spreadsheet->getActiveSheet()->toArray();

           foreach ($data as $row) {
               $insert_data = array(
                   ':nom' => $row[0],
                   ':prenom' => $row[1],
                   ':numero' => $row[2],
                   ':courier_id' => $id_courrier,
                   ':valide' => 1
               );

               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide) VALUES ( :nom, :prenom, :numero, :courier_id, :valide)";
               $statement = $pdo->prepare($query);
               $statement->execute($insert_data);
           }

           return $this->redirectToRoute("send");
       }

       return $this->render('after_log/page.html.twig',[
           "form" => $form->createView(),
           "user" => $user,
           "listeCourrier" => $CourrierRepository->findBy(array(),
                                                          array('created_at' =>'desc'),
                                                          4,0)
       ]);

    }
    /**
     * @Route("/courrier", name="courrier")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('courrier/index.html.twig', [
            "user" => $user,
        ]);
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(Request $request, FlashyNotifier $flashy, CourrierRepository $CourrierRepository):Response
    {
        try{
           $pdo=new PDO("mysql:host=localhost;dbname=stage2","root","");

       }
       catch(PDOException $e){
           echo $e->getMessage();
       }
        $courrier = new Courrier();
        $user = $this->getUser();
        $form = $this->createForm(CourrierType::class,$courrier);
        $form->handleRequest($request);

     if($form->isSubmitted() && $form->isValid())
       {
           $file = $courrier->getFichier();
           $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
           $typemimes = $file->getClientMimeType();
           // this is needed to safely include the file name as part of the URL
           $newFilename = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

           // envoye du fichier sur la base de données
           //  $filename = $file->getClientOriginalName();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename);
           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename);

           //prendre l'envoyeur
           $courrier->setSender($this->getUser());
           $em =$this->getDoctrine()->getManager();
           $em->persist($courrier);
           $em->flush();;

            //mercuryflash
        $flashy->success('Courrier envoyé avec succès!');
          $id_courrier = $courrier->getId();

           //si le fichier existe
           $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
           $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

           $spreadsheet = $reader->load($UploadedFile->getPathname());
           $data = $spreadsheet->getActiveSheet()->toArray();

           foreach ($data as $row) {
               $insert_data = array(
                   ':nom' => $row[0],
                   ':prenom' => $row[1],
                   ':numero' => $row[2],
                   ':courier_id' => $id_courrier,
                   ':valide' => 1
               );

               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide) VALUES ( :nom, :prenom, :numero, :courier_id, :valide)";
               $statement = $pdo->prepare($query);
               $statement->execute($insert_data);
             }

            return $this->redirectToRoute("send");
        }


        return $this->render("courrier/send.html.twig",[
            "form" => $form->createView(),
            "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array(),
                array('created_at' =>'desc'),
                4,0)
        ]);
    }
    /**
     * @Route("/received", name="received")
     */
    public function received(Request $request, FlashyNotifier $flashy, CourrierRepository $CourrierRepository): Response
    {

        try{
           $pdo=new PDO("mysql:host=localhost;dbname=stage2","root","");

       }
       catch(PDOException $e){
           echo $e->getMessage();
       }

        $courrier = new Courrier();
       $user = $this->getUser();
       $form = $this->createForm(CourrierType::class,$courrier);
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid())
       {
           $file = $courrier->getFichier();
           $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
           $typemimes = $file->getClientMimeType();
           // this is needed to safely include the file name as part of the URL
           $newFilename = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

           // envoye du fichier sur la base de données
           //  $filename = $file->getClientOriginalName();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename);
           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename);

           //prendre l'envoyeur
           $courrier->setSender($this->getUser());
           $em =$this->getDoctrine()->getManager();
           $em->persist($courrier);
           $em->flush();
             $id_courrier = $courrier->getId();

           //si le fichier existe
           $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
           $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

           $spreadsheet = $reader->load($UploadedFile->getPathname());
           $data = $spreadsheet->getActiveSheet()->toArray();

           foreach ($data as $row) {
               $insert_data = array(
                   ':nom' => $row[0],
                   ':prenom' => $row[1],
                   ':numero' => $row[2],
                   ':courier_id' => $id_courrier,
                   ':valide' => 1
               );

               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide) VALUES ( :nom, :prenom, :numero, :courier_id, :valide)";
               $statement = $pdo->prepare($query);
               $statement->execute($insert_data);
             }
           //mercuryflash
        $flashy->success('Courrier envoyé avec succès!');

            return $this->redirectToRoute("send");
      }
        return $this->render('courrier/received.html.twig',[
            "form" => $form->createView(),
             "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array(),
                array('created_at' =>'desc'),
                4,0),


            
        ]);
    }
    /**
     * @Route("/excel/{id}", name="excel",methods={"GET"})
     */
    public function excel_show(Request $request, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository): Response
    {

        $user = $this->getUser();
        $upload = new Upload();
        $form = $this->createForm(UploadType::class,$upload);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();
        }


        $user = $this->getUser();
        $file = $courrier ->getFichier();
        $path = $this->getParameter('upload_directory').'\\'.$file;
        $info = new \SplFileInfo($path);

       // $fileSystem = new Filesystem();
        //$fileSystem->chmod($info->getPath(),7777);

        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($info->getPathname());
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

        $spreadsheet = $reader->load($info->getPathname());
        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        //$message = $writer->save('php://output');


        $id_courrier = $courrier->getId();
        $request = $uploadRepository->findBy(["id" => $id_courrier]);
        //$ic_lo =  $CourrierRepository->findBy(["id" => $id_courrier]);
        //var_dump($courri); die;

        $arrayDataExcel = $spreadsheet->getActiveSheet()->toArray();
        //dump($arrayDataExcel);die;

        //echo $message;

        return $this->render('courrier/exShow.html.twig',[
            "user" => $user,
            "form" => $form->createView(),
            "listeCourrier" => $CourrierRepository->findBy(array(),
                array('created_at' =>'desc'),
                4,0),
            'upload' => $uploadRepository->findBy(["courier" => ["id" => $id_courrier]]),
        ]);
    }

    /**
     * @Route("/ajax-excel", name="ajax_excel",methods={"POST"})
     */
    public function ajax_excel_show(Request $request): Response
    {
        $id = $request->request->get('id');

        $user = $this->getUser();
        $CourrierRepository = CourrierRepository::find($id); //@Todo
        dump($CourrierRepository);die;
        $file = $courrier ->getFichier(); // @ToDo requette pour obtenir le courier
        $path = $this->getParameter('upload_directory').'\\'.$file;
        $info = new \SplFileInfo($path);

        //$fileSystem = new Filesystem();
        //$fileSystem->chmod($info->getPath(),7777);

        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($info->getPathname());
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

        $spreadsheet = $reader->load($info->getPathname());
        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        $message = $writer->save('php://output');
        // $message = $writer->save($info->getPathname());


        $arrayDataExcel = $spreadsheet->getActiveSheet()->toArray();
        //dump($arrayDataExcel);die;

        //echo $message;
        return new Response ("ok");
    }

    //ajax_validate_courier
    /**
     * @Route("/ajax-validate-courier", name="ajax_validate_courier",methods={"POST"})
     */
    public function ajax_validate_courier(Request $request, UploadRepository $uploadRepository): Response
    {
        $id = $request->request->get('id'); // recuperation de données envoyer par POST
        $upload = $uploadRepository->find($id);
        $upload->setValide(!$upload->getValide());
        $em =$this->getDoctrine()->getManager();
        $em->persist($upload);
        $em->flush();

        return new Response ("ok");
    }


}