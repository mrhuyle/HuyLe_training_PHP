<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Faker\Factory;

$db = new Util\Database();
$pdo = $db->get_connection();

$conn = get_database_connection();

$faker = Factory::create();

echo $faker->name;
