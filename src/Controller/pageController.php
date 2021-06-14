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
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use PDOException;
use MercurySeries\FlashyBundle\FlashyNotifier;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as files;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


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
           $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");

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
           //$newFilename2 = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);

           //dump($test->getPathname());die;
           $courrier->setFichier($newFilename2);

           //prendre l'envoyeur
           $courrier->setSender($this->getUser());
           $em =$this->getDoctrine()->getManager();
           $em->persist($courrier);
           $em->flush();
           //mercuryflash
           // $flashy->success('Courrier envoyé avec succès!');
        
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
                   ':is_disabled' => 0,
                   ':n_bl' => '',
                   ':second_valide' => 0,
                   ':valide_recipient' => 0,
               );



               $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled, n_bl, second_valide, valide_recipient) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled, :n_bl, :second_valide, :valide_recipient)";
               $statement = $pdo->prepare($query);
               $statement->execute($insert_data);
             }
           


          return $this->redirectToRoute("verify",['id' => $courrier->getId()]);
       }

       return $this->render('after_log/page.html.twig',[
            
           "form" => $form->createView(),
           "user" => $user,
           "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
               array('created_at' =>'desc'),
               4,0),
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
          $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");

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
            //$newFilename2 = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
            $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);

            //dump($test->getPathname());die;
            $courrier->setFichier($newFilename2);

            //prendre l'envoyeur
            $courrier->setSender($this->getUser());
            $em =$this->getDoctrine()->getManager();
            $em->persist($courrier);
            $em->flush();
            //mercuryflash
           // $flashy->success('Courrier envoyé avec succès!');

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
                    ':is_disabled' => 0,
                    ':n_bl' => '',
                    ':second_valide' => 0,
                    ':valide_recipient' => 0,
                );



                $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled, n_bl, second_valide, valide_recipient) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled, :n_bl, :second_valide, :valide_recipient)";
                $statement = $pdo->prepare($query);
                $statement->execute($insert_data);
            }



            return $this->redirectToRoute("verify",['id' => $courrier->getId()]);
        }



        return $this->render("courrier/send.html.twig",[
            "form" => $form->createView(),
            "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
        ]);
    }
    /**
     * @Route("/received", name="received")
     */
    public function received(Request $request, FlashyNotifier $flashy, CourrierRepository $CourrierRepository): Response
    {

        try{
           $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");

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
            //$newFilename2 = $originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
            $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);

            //dump($test->getPathname());die;
            $courrier->setFichier($newFilename2);

            //prendre l'envoyeur
            $courrier->setSender($this->getUser());
            $em =$this->getDoctrine()->getManager();
            $em->persist($courrier);
            $em->flush();
            //mercuryflash
            //$flashy->success('Courrier envoyé avec succès!');

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
                    ':is_disabled' => 0,
                    ':n_bl' => '',
                    ':second_valide' => 0,
                    ':valide_recipient' => 0,
                );



                $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled, n_bl, second_valide, valide_recipient) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled, :n_bl, :second_valide, :valide_recipient)";
                $statement = $pdo->prepare($query);
                $statement->execute($insert_data);
            }



            return $this->redirectToRoute("verify",['id' => $courrier->getId()]);
        }

        return $this->render('courrier/received.html.twig',[
            "form" => $form->createView(),
             "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
            //'upload' => $uploadRepository->findBy(["courier" => ["id" => $id_courrier]])


            
        ]);
    }
    /**
     * @Route("/excel/{id}", name="excel",methods={"GET","POST"})
     */
    public function excel_show(Request $request,FlashyNotifier $flashy, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository): Response
    {


        try{
            $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");

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
            $cour->setNomC($courrier->getNomC().'(rapport d\'erreurs)');
            $cour->setRecipient($courrier->getSender());

            $fichier = new File($courrier->getFichier());
            //$cour->setFichier($fichier->getPathname());
            $cour->setStatus($courrier->getStatus());
            $cour->setIsVerify(true);

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
            $cour->setSender($this->getUser());
            $em =$this->getDoctrine()->getManager();
            $em->persist($cour);
            $em->flush();
            $flashy->success('rapport d\'erreur du courrier envoyé avec succès!');

            $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );

            $spreadsheet = $reader->load($UploadedFile->getPathname());
            $data = $spreadsheet->getActiveSheet()->toArray();
            $id_courrier = $courrier->getId();
            $requete =  "SELECT nom,prenom,numero,courier_id,valide,is_disabled, n_bl, second_valide,valide_recipient FROM upload where courier_id = ".$id_courrier;
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
                            ':is_disabled' => 0,
                            ':n_bl' => $donnees2['n_bl'],
                            ':second_valide' => $donnees2['second_valide'],
                            ':valide_recipient' => $donnees2['valide_recipient']
               );

                        $query = "INSERT INTO upload ( nom, prenom, numero, courier_id, valide, is_disabled, n_bl, second_valide, valide_recipient) VALUES ( :nom, :prenom, :numero, :courier_id, :valide, :is_disabled, :n_bl, :second_valide, :valide_recipient)";
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
            "courrier" => $courrier,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
            'upload' => $uploadRepository->findBy(["courier" => ["id" => $id_courrier]]),
        ]);
    }

     /**
     * @Route("/verify/{id}", name="verify",methods={"GET","POST"})
     */
    public function verify(Request $request, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository): Response
    {

        try {
            $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
       // dump($courrier->getFichier());
        //dump($courrier);die;
        $user = $this->getUser();
        $id_courrier = $courrier->getId();


      /*  if($courrier->getFichier());
        {
            $file = new File($courrier->getFichier());
            //$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            //$typemimes = $file->getClientMimeType();
            // this is needed to safely include the file name as part of the URL
            // $newFilename2 = $this->getParameter('upload_directory') . '/' . $originalFilename . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $UploadedFile = $file->move($this->getParameter('upload_directory'), $file);

            //dump($test->getPathname());die;
            $courrier->setFichier($file->getPathname());

            //si le fichier existe
            $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);

            $spreadsheet = $reader->load($UploadedFile->getPathname());
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                $insert_data = array(
                    ':nom' => $row[0],
                    ':prenom' => $row[1],
                    ':numero' => $row[2],
                    ':courier_id' => $id_courrier,
                    ':valide' => 0,
                    ':is_disabled' => 0,
                    ':n_bl' => '',
                );
            }
        }*/
     

            //fin du test

            return $this->render('courrier/verification.html.twig', [
                "courrier" => $courrier,
               //"data" => $data,
                "user" => $user,
                "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                    array('created_at' =>'desc'),
                    4,0),
                'upload' => $uploadRepository->findBy(["courier" => ["id" => $id_courrier]]),
            ]);
        }
    /**
     * @Route("/verify-second/{id}", name="second_verification",methods={"GET","POST"})
     */
    public function second_verification(Request $request, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository): Response
    {
        $user = $this->getUser();
        $id_courrier = $courrier->getId();


        return $this->render('courrier/secondV.html.twig', [
            "courrier" => $courrier,
            //"data" => $data,
            "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
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
        $upload->setValideRecipient(!$upload->getValideRecipient());
        $em =$this->getDoctrine()->getManager();
        $em->persist($upload);
        $em->flush();

        return new Response ("ok");
    }


    /**
     * @Route("/ajax-add-line", name="ajax_add_line",methods={"GET","POST"})
     */
    public function ajax_add_line(Request $request,EntityManagerInterface $entityManager,CourrierRepository $courrierRepository): Response
    {
        //recupération des entrées de l'utilisateur depuis ajax
        $tab = $request->request->get('tab');
        $id = $request->request->get('id');
        $tab_d = $request->request->get('tab_d');
        $dis = false;

        //update avec NBL et valide des lignes d'entrée
        for ($j = 0; $j < count($tab_d); $j++) {
        $piece = $entityManager->getRepository(Upload::class)->find(
            $tab_d[$j]['id_l']);
        $piece->setNBL($tab_d[$j]['N° BL_D']);
        $piece->setValide($tab_d[$j]['check_D']);
        $entityManager->flush();
        }

        //ajout des nouveaux lignes
       $id_co = $courrierRepository->findBy(["id" => $id ]);
            for ($i = 0; $i < count($tab); $i++) {
                $upload = new Upload();
               $upload->setNom($tab[$i]['nom']);
               $upload->setPrenom($tab[$i]['prenom']);
               $upload->setNumero($tab[$i]['numero']);
               $upload->setValide($tab[$i]['boxcheck']);
               $upload->setIsDisabled($dis);
               $upload->setCourier($id_co[0]);
               $upload->setNBl($tab[$i]['N_BL']);
               $upload->setSecondValide(false);
               $upload->setValideRecipient(false);

                $em =$this->getDoctrine()->getManager();
                $em->persist($upload);
                $em->flush();}


            return new Response ('ok');
        }

    /**
     * @Route("/ajax-delete-line", name="ajax_delete_line",methods={"POST"})
     */
    public function ajax_delete_line(Request $request,EntityManagerInterface $entityManager,CourrierRepository $courrierRepository): Response
    {
        $tab_del = $request->request->get('tad');
        dump($tab_del[0]["check"]);
        for($i = 0; $i< count($tab_del) ; $i++)
        {
            $piece_del = $entityManager->getRepository(Upload::class)->find($tab_del[$i]['id_d']);
            $em =$this->getDoctrine()->getManager();
            $em->remove($piece_del);
            $em->flush();
        }
        return new Response ('ok');
    }

    /**
     * @Route("/ajax-validation", name="ajax_validation",methods={"POST"})
     */
    public function ajax_validation(Request $request,EntityManagerInterface $entityManager,CourrierRepository $courrierRepository): Response
    {
        $res = $request->request->get('res');
        $st = $request->request->get('second_tab');

        if($res)
        {
            //update de second_valide de piece
           for ($j = 0; $j < count($st); $j++) {
                $piece = $entityManager->getRepository(Upload::class)->find($st[$j]['id']);
                $piece->setSecondValide($st[$j]['checkbo']);
                $entityManager->flush();
            }

           //update is_verify de courrier
            $piece = $entityManager->getRepository(Upload::class)->find($st[0]['id']);
            $id_cour = $piece->getCourier()->getId();
            $couri = $entityManager->getRepository(Courrier::class)->find($id_cour);
            $couri->setIsVerify(1);
            $entityManager->flush();


        }
        return new Response ('ok');
    }



    /**
     * @Route("/ajax-disabled-courier", name="ajax_disabled_courier",methods={"POST"})
     */
    public function ajax_disabled_courier(Request $request,EntityManagerInterface $entityManager, UploadRepository $uploadRepository): Response
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

        //courrier valider
      $up = $uploadRepository->find($ids[0]);
        $couri = $entityManager->getRepository(Courrier::class)->find($up->getCourier()->getId());
        $couri->setValider(1);
        $entityManager->flush();


    // si php -> twig
       // $response = json_encode($nom);
        return new Response ('ok');
    }


}