<?php

// ==================================================
// IMPORTS
// ==================================================
      // Importar Faker
      require_once '../assets/modules/faker/vendor/autoload.php';
      use Faker\Factory;

// ==================================================
// FUNCIONES
// ==================================================

      // Generar personas ficticias
            // IN: generateFakeProfiles(int)
            // OUT: /assets/data/fake_profiles.json
            function generateFakeProfiles($number) {
                  // Crear una instancia de Faker con datos españoles
                  $faker = Factory::create('es_ES');

                  // Opciones predefinidas
                  $sexes = ['Hombre', 'Mujer', 'No binario'];
                  $sexualOrientations = ['Heterosexual', 'Homosexual', 'Bisexual'];

                  // Rutas imágenes
                  $imageDirectory = '../assets/img/seeder/';
                  $imageFiles = glob($imageDirectory . '*.webp', GLOB_BRACE);

                  // Array para almacenar datos generados
                  $data = [];

                  for ($i = 0; $i < $number; $i++) {
                        // Definir datos
                        $name = $faker->firstName;
                        $lastName = $faker->lastName;
                        $alias = $faker->userName;
                        $birthDate = $faker->date('Y-m-d');
                        $latitude = $faker->latitude(40, 43); // Latitud entre 40 y 43 (España)
                        $longitude = $faker->longitude(-5, 3); // Longitud entre -5 y 3 (España)
                        $sex = $sexes[array_rand($sexes)];
                        $sexualOrientation = $sexualOrientations[array_rand($sexualOrientations)];

                        // Seleccionar dos imágenes de perfil aleatorias
                        $profileImages = array_rand($imageFiles, 2);
                        $profileImage1 = $imageFiles[$profileImages[0]];
                        $profileImage2 = $imageFiles[$profileImages[1]];

                        // Agregar datos al array
                        $data[] = [
                              'nom' => $name,
                              'cognoms' => $lastName,
                              'alias' => $alias,
                              'data_naixement' => $birthDate,
                              'ubicacio' => [
                                    'latitud' => $latitude,
                                    'longitud' => $longitude
                              ],
                              'sexe' => $sex,
                              'orientacio_sexual' => $sexualOrientation,
                              'profile_images' => [
                                    'image1' => $profileImage1,
                                    'image2' => $profileImage2
                              ]
                        ];
                  }

                  // Guardar datos en .json
                  $jsonFilePath = '../assets/data/fake_profiles.json';
                  file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            }

      // Seguir aquí próxima función...
            // IN: 
            // OUT: 
  
?>


