<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\PictureLike;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AppFixtures extends Fixture
{


    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }


    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        // Admin

        $admin = new User();

        $users = [];
        $pictures = [];
        $comments = [];
        $likes = [];


        // function pour prendre un utilisateur aléatoire

        function array_random($array, $amount = 1)
        {
            $keys = array_rand($array, $amount);

            if ($amount == 1) {
                return $array[$keys];
            }

            $results = [];
            foreach ($keys as $key) {
                $results[] = $array[$key];
            }

            return $results;
        }

        $admin->setEmail('Ay0r0ss@hotmail.com');
        $admin->setNickName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->encoderFactory->getEncoder(User::class)->encodePassword('admin', null));

        $manager->persist($admin);

        // Category

        $category1 = new Category();
        $category1->setName('Sport');

        $category2 = new Category;
        $category2->setName('Nature');

        $category3 = new Category;
        $category3->setName('Science');

        $category4 = new Category;
        $category4->setName('Art');

        $category5 = new Category;
        $category5->setName('History');

        $category6 = new Category;
        $category6->setName('Music');

        $category6 = new Category;
        $category6->setName('clothing');


        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->persist($category4);
        $manager->persist($category5);
        $manager->persist($category6);

        // User

        for ($i = 0; $i < 20; $i++) {

            $user = new User();

            $user->setEmail($i . 'user@hotmail.com');
            $user->setNickName('user' . $i);
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);
            $user->setPassword($this->encoderFactory->getEncoder(User::class)->encodePassword('user', null));

            $users[] = $user;

            $manager->persist($user);

        }

        /*
        * output the value for the first random index
        * you can access the first index with $randIndex[0]
        * (may be a bit confusing for programming beginners)
        */

        // Picture

        for ($i = 0; $i < 20; $i++) {

            $picture = new Picture();

            $picture->setTitle($i . 'Title');
            $picture->setDescription($i . 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l\'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte. Il n\'a pas fait que survivre cinq siècles, mais s\'est aussi adapté à la bureautique informatique, sans que son contenu n\'en soit modifié. Il a été popularisé dans les années 1960 grâce à la vente de feuilles Letraset contenant des passages du Lorem Ipsum, et, plus récemment, par son inclusion dans des applications de mise en page de texte, comme Aldus PageMaker.');
            $picture->setUser(array_random($users));

            $pictures[] = $picture;

            $manager->persist($picture);
        }

        // Comment

        for ($i = 0; $i < 40; $i++) {

            $comment = new Comment();

            $comment->setTextComment($i . 'On sait depuis longtemps que travailler avec du texte lisible et contenant du sens est source de distractions, et empêche de se concentrer sur la mise en page elle-même. L\'avantage du Lorem Ipsum sur un texte générique comme \'Du texte. Du texte. Du texte.\' est qu\'il possède une distribution de lettres plus ou moins normale');
            $comment->setUser(array_random($users));
            $comment->setPicture(array_random($pictures));

            $comments[] = $comment;

            $manager->persist($comment);
        }


        // Like

        for ($i = 0; $i < 40; $i++) {

            $like = new PictureLike();

            $like->setTextComment($i . 'On sait depuis longtemps que travailler avec du texte lisible et contenant du sens est source de distractions, et empêche de se concentrer sur la mise en page elle-même. L\'avantage du Lorem Ipsum sur un texte générique comme \'Du texte. Du texte. Du texte.\' est qu\'il possède une distribution de lettres plus ou moins normale');
            $like->setUser(array_random($users));
            $like->setPicture(array_random($pictures));

            $likes[] = $like;

            $manager->persist($like);
        }

        $manager->flush();
    }


}
