<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TodoListFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        #todo mettre dans utils
        function random_color_part() {
            return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
        }

        function random_color() {
            return "#" .random_color_part() . random_color_part() . random_color_part();
        }

        for($i = 0; $i < 10; $i++){
            $category = new Category();
            $category->setLabel('Category '.$i);
            $category->setColor(random_color());
            $manager->persist($category);

            $task = new Task();
            $task->setLabel('Task '.$i);
            $task->setCategory($category);
            $manager->persist($task);
        }


        // $manager->persist($product);

        $manager->flush();
    }
}
