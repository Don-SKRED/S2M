<?php

namespace App\Form;

use App\Entity\Courrier;
use App\Repository\CourrierRepository;
use App\Repository\UserRepository;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourrierType extends AbstractType
{

/*public function __construct (User $user)
{
    $this->user = $user;
}*/

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       // $user = $options['user'];
            //$user =$this->user;
        $builder
            ->add('nom_c',TextType::class,[
                'required' => false,
                'label' => 'Nom du courrier',
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('notes', TextareaType::class,[

                "attr" => [
                    "class" => "form-control"
                ]
            ])

            ->add('status',ChoiceType::class,[
                'required' => false, //essayer de mettre required false sur twig ou form du controller
                "choices" =>[
                    'Très urgent' => 'TRES URGENT',
                    'Urgent' => 'URGENT',
                    'Pas urgent' => 'PAS URGENT',
                ],
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('fichier',FileType::class,[
               // "data_class" => null,
                'required' => false,
                'label' => 'choississez votre fichier',
                "attr" => [
                    "class" => "form-control",
                    "accept" => ".csv, .xlsx, .xls",

                ],
               'constraints' => [
                  new File([
                      'mimeTypes' => [
                            "application/vnd.ms-excel",
                            "application/msexcel",
                            "application/x-msexcel",
                            "application/x-ms-excel",
                            "application/x-excel",
                            "application/x-dos_ms_excel",
                            "application/xls",
                            "application/x-xls",
                            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                            "text/x-csv",
                            "application/csv",
                            "application/x-csv",
                            "text/csv",
                            "text/plain",
                            "text/comma-separated-values",
                            "text/x-comma-separated-values",
                            "text/tab-separated-values",  
                            

                      ],

                      'mimeTypesMessage' => 'Uploader un document PDF valid',
                  ])
            ]
                ])


            ->add('recipient',EntityType::class,[
                'required' => false,
                "class" => User::class,
                'label' => 'Destinataire',
                "choice_label" => "Nom",
              /* 'query_builder' => function(UserRepository $repository) use($user){
                    return $repository->findOtherUser($user);},*/
                "attr" => [
                    "class" => "form-control"
                ]])
              /*  "choice_filter" => function( $user =  $this->getUser()){

                     $l_destinataires = $entityManager->getRepository(User::class)->findOtherUser($user);
                     return $l_destinataires;
                }*/
               /* 'query_builder' => function(CourrierRepository $cr) {
                     $user = $this->getUser();
                  return  $er->createQueryBuilder('c')
                 ->where('c.recipient != :recipient')
                 ->setParameter('recipient',  $user)
                ->orderBy('c.recipient', 'ASC');
          
                }*/
            
            ->add ('envoyer', SubmitType::class,[
                "attr" => [
                    "class" => "btn btn-outline-warning waves-effect\""

                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Courrier::class,
            //'user' => false,

        ]);
    }
}
