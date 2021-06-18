<?php
namespace App\Controller;

use App\Entity\Pieces;
use App\Entity\Upload;
use App\Entity\User;

use App\Repository\CourrierRepository;
use App\Repository\PiecesRepository;
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
           $newFilename2 = $this->getParameter('upload_directory').'/'.$originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
           $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);
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
                   ':courrier_id' => $id_courrier,
                   ':n_cmd' => $row[0],
                   ':n_recept' => $row[1],
                   ':n_bl' => $row[2],
                   ':fournisseur' => $row[3],
                   ':rayon' => $row[4],
                   ':d_reception' => $row[5],
                   ':montant_ht' => $row[6],
                   ':valide' => 0,
                   ':is_disabled' => 0,
                   ':second_valide' => 0,
                   ':valide_recipient' => 0,
               );
               $query = "INSERT INTO pieces ( courrier_id, n_cmd, n_recept, n_bl, fournisseur, rayon, d_reception, montant_ht, valide, is_disabled, second_valide, valide_recipient) VALUES ( :courrier_id, :n_cmd, :n_recept, :n_bl, :fournisseur, :rayon, :d_reception, :montant_ht, :valide, :is_disabled, :second_valide, :valide_recipient)";
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
     * @Route("/send", name="send")
     */
    public function send(Request $request, FlashyNotifier $flashy, CourrierRepository $CourrierRepository):Response
    {
        try
        {
          $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");
        }
       catch(PDOException $e)
       {
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
           // $flashy->success('Courrier envoyé avec succès!');
            $id_courrier = $courrier->getId();

            //si le fichier existe
            $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($UploadedFile->getPathname());
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type );
            $spreadsheet = $reader->load($UploadedFile->getPathname());
            $data = $spreadsheet->getActiveSheet()->toArray();

            foreach ($data as $row) {
                $insert_data = array(
                    ':courrier_id' => $id_courrier,
                    ':n_cmd' => $row[0],
                    ':n_recept' => $row[1],
                    ':n_bl' => $row[2],
                    ':fournisseur' => $row[3],
                    ':rayon' => $row[4],
                    ':d_reception' => $row[5],
                    ':montant_ht' => $row[6],
                    ':valide' => 0,
                    ':is_disabled' => 0,
                    ':second_valide' => 0,
                    ':valide_recipient' => 0,
                );
                $query = "INSERT INTO pieces ( courrier_id, n_cmd, n_recept, n_bl, fournisseur, rayon, d_reception, montant_ht, valide, is_disabled, second_valide, valide_recipient) VALUES ( :courrier_id, :n_cmd, :n_recept, :n_bl, :fournisseur, :rayon, :d_reception, :montant_ht, :valide, :is_disabled, :second_valide, :valide_recipient)";
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

        try
        {
           $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");
        }
        catch(PDOException $e)
        {
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
            $newFilename2 = $this->getParameter('upload_directory').'/'.$originalFilename.'-'.uniqid().'.'.$file->getClientOriginalExtension();
            $UploadedFile = $file->move($this->getParameter('upload_directory'), $newFilename2);
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
                    ':courrier_id' => $id_courrier,
                    ':n_cmd' => $row[0],
                    ':n_recept' => $row[1],
                    ':n_bl' => $row[2],
                    ':fournisseur' => $row[3],
                    ':rayon' => $row[4],
                    ':d_reception' => $row[5],
                    ':montant_ht' => $row[6],
                    ':valide' => 0,
                    ':is_disabled' => 0,
                    ':second_valide' => 0,
                    ':valide_recipient' => 0,
                );
                $query = "INSERT INTO pieces ( courrier_id, n_cmd, n_recept, n_bl, fournisseur, rayon, d_reception, montant_ht, valide, is_disabled, second_valide, valide_recipient) VALUES ( :courrier_id, :n_cmd, :n_recept, :n_bl, :fournisseur, :rayon, :d_reception, :montant_ht, :valide, :is_disabled, :second_valide, :valide_recipient)";
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
        ]);
    }
    /**
     * @Route("/excel/{id}", name="excel",methods={"GET","POST"})
     */
    public function excel_show(EntityManagerInterface $entityManager,Request $request,FlashyNotifier $flashy, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository, PiecesRepository $piecesRepository): Response
    {
        try {
            $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        dump($courrier);
        dump($this->getUser());
        $recept = $courrier->getSender();

        $id_courrier = $courrier->getId();
        //reception d'erreur
        $eNotes = $request->request->get('errnotes');
        //courrier et courier
        $courier = $entityManager->getRepository(Courrier::class)->find( $id_courrier);
        //le courrier est lu donc
        $courrier->setIsRead(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($courrier);
        $em->flush();
        //atao crochet le izy
        //lase renomer le nom courrier(rapport d'erreur) refa manao retour
        $nomC = explode('[',$courrier->getNomC());
        $n = $courrier->getNotes() . '/' . $eNotes;
        if ($eNotes != null) {
            $courier->setNotes($n);
                if (count($nomC)>1)
                {
                    $courier->setNomC($courrier->getNomC());
                }
                else{
                    $courier->setNomC($courrier->getNomC()."[rapport d'erreur]");
                }
                $courier->setIsRead(0);
                $courier->setSender($courrier->getRecipient());
                $courier->setRecipient($recept);
                $courier->setCreatedAt(new \DateTime());
                //de le envoyeur si le recepteur mivadika
                // le courrier zany lasa miala ao amle reception de ny send no mitombo
                $entityManager->flush();
            }
        $em = $this->getDoctrine()->getManager();
        $em->persist($courrier);
        $em->flush();
      //  $flashy->success('rapport d\'erreur du courrier envoyé avec succès!');

        $user = $this->getUser();
        return $this->render('courrier/exShow.html.twig',[
            //  "form" => $form->createView(),
            "user" => $user,
            "courrier" => $courrier,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
            'pieces' => $piecesRepository->findBy(["courrier" => ["id" => $id_courrier]]),
        ]);
    }

     /**
     * @Route("/verify/{id}", name="verify",methods={"GET","POST"})
     */
    public function verify(Request $request, Courrier $courrier, CourrierRepository $CourrierRepository, PiecesRepository $piecesRepository): Response
    {

        try
        {
            $pdo = new PDO("mysql:host=localhost;port=3308;dbname=stage2", "root", "");

        } catch (PDOException $e)
        {
            echo $e->getMessage();
        }
        $user = $this->getUser();
        $id_courrier = $courrier->getId();

                 return $this->render('courrier/verification.html.twig', [
                "courrier" => $courrier,
               //"data" => $data,
                "user" => $user,
                "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                    array('created_at' =>'desc'),
                    4,0),
                     'pieces' => $piecesRepository->findBy(["courrier" => ["id" => $id_courrier]]),

            ]);
        }
    /**
     * @Route("/verify-second/{id}", name="second_verification",methods={"GET","POST"})
     */
    public function second_verification(Request $request, Courrier $courrier, CourrierRepository $CourrierRepository, UploadRepository $uploadRepository,PiecesRepository $piecesRepository): Response
    {
        $user = $this->getUser();
        $id_courrier = $courrier->getId();

        return $this->render('courrier/secondV.html.twig', [
            "courrier" => $courrier,
            "user" => $user,
            "listeCourrier" => $CourrierRepository->findBy(array('is_verify' => 'true'),
                array('created_at' =>'desc'),
                4,0),
            'pieces' => $piecesRepository->findBy(["courrier" => ["id" => $id_courrier]],["id" => 'desc']),
        ]);
    }


    //Tous les traitements Ajax sont effectues ci-dessous
    /**
     * @Route("/ajax-validate-courier", name="ajax_validate_courier",methods={"POST"})
     */
    public function ajax_validate_courier(Request $request, PiecesRepository $piecesRepository): Response
    {

        $id = $request->request->get('id'); // recuperation de données envoyer par POST
        $piece = $piecesRepository->find($id);
        $piece->setValideRecipient(!$piece->getValideRecipient());
        $em =$this->getDoctrine()->getManager();
        $em->persist($piece);
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
        $piece = $entityManager->getRepository(Pieces::class)->find($tab_d[$j]['id_l']);
        $piece->setNBL($tab_d[$j]['N° BL_D']);
        if($tab_d[$j]['check_D'] == "true")
        $piece->setValide(1);
        else
            $piece->setValide(0);
        $entityManager->flush();
        }

        //ajout des nouveaux lignes
       $id_co = $courrierRepository->findBy(["id" => $id ]);
        if ($tab != null) {

            for ($i = 0; $i < count($tab); $i++) {
                $pieces = new Pieces();

              $pieces->setCourrier($id_co[0]);
              $pieces->setNCmd($tab[$i]['n_cmd']);
              $pieces->setNRecept($tab[$i]['n_recept']);
              $pieces->setNBl($tab[$i]['N_BL']);
              $pieces->setFournisseur($tab[$i]['fournisseur']);
              $pieces->setDReception($tab[$i]['d_reception']);
              $pieces->setRayon($tab[$i]['rayon']);
              $pieces->setMontantHT($tab[$i]['montant_ht']);
              $pieces->setIsDisabled($dis);
               if($tab[$i]['boxcheck'] == "true")
                   $pieces->setValide(1);
               else
                   $pieces->setValide(0);
              $pieces->setSecondValide(0);
              $pieces->setValideRecipient(0);
               $em = $this->getDoctrine()->getManager();
                $em->persist($pieces);
                $em->flush();
            }
        }
        else{
            ECHO 'rien à ajouter';
        }
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
            $piece_del = $entityManager->getRepository(pieces::class)->find($tab_del[$i]['id_d']);
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
                $piece = $entityManager->getRepository(Pieces::class)->find($st[$j]['id']);
                $piece->setSecondValide($st[$j]['checkbo']);
               if($st[$j]['checkbo'] == "true")
                   $piece->setSecondValide(1);
               else
                   $piece->setSecondValide(0);
                $entityManager->flush();
            }
           //update is_verify de courrier
            $pieces = $entityManager->getRepository(Pieces::class)->find($st[0]['id']);
            $id_cour = $pieces->getCourrier()->getId();
            $couri = $entityManager->getRepository(Courrier::class)->find($id_cour);

            $couri->setIsVerify(1);
            $entityManager->flush();
        }
        return new Response ('ok');
    }

    /**
     * @Route("/ajax-disabled-courier", name="ajax_disabled_courier",methods={"POST"})
     */
    public function ajax_disabled_courier(Request $request,EntityManagerInterface $entityManager, PiecesRepository $piecesRepository): Response
    {
        $ids = $request->request->get('ids'); // recuperation de données envoyer par POST
        foreach ($ids as $id) {

            $pieces = $piecesRepository->find($id);

            //$upload->setIsDisabled(!$upload->getIsDisabled());
            $pieces->setIsDisabled(true);
            $em =$this->getDoctrine()->getManager();
            $em->persist($pieces);
            $em->flush();
        }

        //courrier valider
      $up = $piecesRepository->find($ids[0]);
        $couri = $entityManager->getRepository(Courrier::class)->find($up->getCourrier()->getId());
        $couri->setValider(1);
        $entityManager->flush();
        
    // si php -> twig
       // $response = json_encode($nom);
        return new Response ('ok');
    }


}