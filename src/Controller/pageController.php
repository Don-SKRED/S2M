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

use Symfony\Component\HttpFoundation\File\File;
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
           $newFilename2 = $this->getParameter('upload_directory').'/'.$originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);

           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename2);

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
                   ':valide' => 0,
                   ':is_disabled' => 0
               );

               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled)";
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
           $newFilename2 = $this->getParameter('upload_directory').'/'.$originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);

           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename2);

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
                   ':valide' => 0,
                   ':is_disabled' => 0
               );

               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled)";
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
           //$newFilename = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();


           // envoye du fichier sur la base de données
           //  $filename = $file->getClientOriginalName();
           //$UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename);

           $newFilename2 = $this->getParameter('upload_directory').'/'.$originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);

           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename2);

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
                   ':valide' => 0,
                   ':is_disabled' => 0
               );

               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled)";
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
     * @Route("/excel/{id}", name="excel",methods={"GET","POST"})
     */
    public function excel_show(Request $request, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository): Response
    {


        try{
            $pdo=new PDO("mysql:host=localhost;dbname=stage2","root","");

        }
        catch(PDOException $e){
            echo $e->getMessage();
        }
        //dump($courrier);die;
        $id_courrier = $courrier->getId();

        $courrier->setIsRead(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($courrier);
        $em->flush();
        //dump($courrier->getStatus());die;
        //trouver un genre de valeur par defaut
        $cour = new Courrier;
        $form = $this->createForm(CourrierType::class,$cour);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $cour->setSender($courrier->getRecipient());
            $cour->setNomC($courrier->getNomC().'(rapport d\'erreur)');
            $cour->setRecipient($courrier->getSender());

            $fichier = new File($courrier->getFichier());
            //$cour->setFichier($fichier->getPathname());
            $cour->setStatus($courrier->getStatus());

            // dump($file = $cour->getFichier()->getPathname());die;
           // $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            //$typemimes = $file->getClientMimeType();
            // this is needed to safely include the file name as part of the URL
           // $newFilename = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();

            // envoye du fichier sur la base de données
            //  $filename = $file->getClientOriginalName();
            $UploadedFile = $fichier->move($this->getParameter('upload_directory'), $fichier);
            //dump($test->getPathname());die;
            //$cour->setFichier($file);
            $cour->setFichier($fichier->getPathname());

            //prendre l'envoyeur
            $courrier->setSender($this->getUser());
            $em =$this->getDoctrine()->getManager();
            $em->persist($cour);
            $em->flush();

            $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

            $spreadsheet = $reader->load($UploadedFile->getPathname());
            $data = $spreadsheet->getActiveSheet()->toArray();
            $id_courrier = $courrier->getId();
            $requete =  "SELECT nom,prenom,numero,courier_id,valide FROM upload where courier_id = ".$id_courrier;
            $reponse2 = $pdo->query($requete);


                    while($donnees2 = $reponse2->fetch()) {


                        //$query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide) VALUES (".$donnees2['nom'].','.$donnees2['prenom'].','.$donnees2['numero'].','.$donnees2['courier_id'].','.$donnees2['valide'].")";
                        //$statement = $pdo->query("INSERT INTO upload ( nom, prenom, numero, courier_id, valide) VALUES (".$donnees2['nom'].','.$donnees2['prenom'].','.$donnees2['numero'].','.$donnees2['courier_id'].','.$donnees2['valide'].")");
                        //$statement->execute($query);
                        $insert_data = array(
                            ':nom' => $donnees2['nom'],
                            ':prenom' => $donnees2['prenom'],
                            ':numero' => $donnees2['numero'],
                            ':courier_id' => $cour->getId(),
                            ':valide' => $donnees2['valide'],
                       ':is_disabled' => 0
               );

                       $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled)";
                      $statement = $pdo->prepare($query);
                        $statement->execute($insert_data);

                    }




            //si le fichier existe

            return $this->redirectToRoute("send");

        }





        $user = $this->getUser();

        //$request = $uploadRepository->findBy(["id" => $id_courrier]);

        return $this->render('courrier/exShow.html.twig',[
            "form" => $form->createView(),
            "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array(),
                array('created_at' =>'desc'),
                4,0),
            'upload' => $uploadRepository->findBy(["courier" => ["id" => $id_courrier]]),
        ]);
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
//ajax_disabled_courier
    /**
     * @Route("/ajax-disabled-courier", name="ajax_disabled_courier",methods={"POST"})
     */
    public function ajax_disabled_courier(Request $request, UploadRepository $uploadRepository): Response
    {
        $ids = $request->request->get('ids'); // recuperation de données envoyer par POST
        //var_dump($ids);die;
        $nom = ['test'];
        foreach ($ids as $id) {
            $upload = $uploadRepository->find($id);
            //$upload->setIsDisabled(!$upload->getIsDisabled());
            $upload->setIsDisabled(true);
            $em =$this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();
        }
    // si php -> twig
       // $response = json_encode($nom);
        return new Response ('ok');
    }


}