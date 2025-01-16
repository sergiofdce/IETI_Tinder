<?php

// ==================================================
// IMPORTS
// ==================================================
// Importar Faker
// require_once '../assets/modules/faker/vendor/autoload.php';
// use Faker\Factory;

// ==================================================
// FUNCIONES
// ==================================================

// Generar personas ficticias
// IN: generateFakeProfiles(int)
// OUT: /assets/data/fake_profiles.json
// function generateFakeProfiles($number) {
//       // Crear una instancia de Faker con datos españoles
//       $faker = Factory::create('es_ES');

//       // Opciones predefinidas
//       $genres = ['home', 'dona', 'no binari'];
//       $sexualOrientations = ['heterosexual', 'homosexual', 'bisexual'];

//       // Rutas imágenes
//       $imageDirectory = '../assets/img/seeder/';
//       $imageFiles = glob($imageDirectory . '*.webp', GLOB_BRACE);

//       // Array para almacenar datos generados
//       $data = [];

//       // Fecha actual y fecha límite para los 18 años
//       $currentDate = date('Y-m-d');
//       $eighteenYearsAgo = date('Y-m-d', strtotime('-18 years', strtotime($currentDate)));

//       for ($i = 0; $i < $number; $i++) {
//             // Definir datos
//             $name = $faker->firstName;
//             $lastName = $faker->lastName;
//             $alias = $faker->userName;

//             // Generar una fecha de nacimiento aleatoria, asegurando que la persona tiene como máximo 18 años
//             $birthDate = $faker->date('Y-m-d', $eighteenYearsAgo);

//             $latitude = $faker->latitude(40, 43); // Latitud entre 40 y 43 (España)
//             $longitude = $faker->longitude(-5, 3); // Longitud entre -5 y 3 (España)
//             $userGenre = $genres[array_rand($genres)];
//             $sexualOrientation = $sexualOrientations[array_rand($sexualOrientations)];

//             // Seleccionar dos imágenes de perfil aleatorias
//             $profileImages = array_rand($imageFiles, 2);
//             $profileImage1 = "assets/img/seeder/" . basename($imageFiles[$profileImages[0]]);
//             $profileImage2 = "assets/img/seeder/" . basename($imageFiles[$profileImages[1]]);

//             // Generar email
//             $email = $alias . '@iesesteveterradas.cat';

//             // Agregar datos al array
//             $data[] = [
//                   'name' => $name,
//                   'surname' => $lastName,
//                   'alias' => $alias,
//                   'birth_date' => $birthDate,
//                   'location' => [
//                         'latitude' => $latitude,
//                         'longitude' => $longitude
//                   ],  // Ahora 'location' es un array con 'latitude' y 'longitude'
//                   'genre' => $userGenre,
//                   'sexual_preference' => $sexualOrientation,
//                   'profile_images' => [
//                         'image1' => $profileImage1,
//                         'image2' => $profileImage2
//                   ],
//                   'email' => $email,
//                   'password' => '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', // Contraseña encriptada
//                   'created_at' => date('Y-m-d H:i:s') // Fecha de creación actual
//             ];
//       }

//       // Guardar datos en .json
//       $jsonFilePath = '../assets/data/fake_profiles.json';
//       file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
// }

      // Generar logs de eventos
      // IN: logEvent($eventType, $description, $userEmail)
      // OUT: /logs/YY.txt
            function logEvent($eventType, $description, $userEmail)
            {
                  // Obtén la fecha actual en formato YYYY-MM-DD
                  $currentDate = date('Y-m-d');

                  // Define la ruta y nombre del archivo de logs con la fecha actual
                  $tempLogsFile = '/logs/' . $currentDate . '.txt';

                  // Crear el log con los datos
                  $log = [
                        'timestamp' => date('Y-m-d H:i:s'), // Fecha y hora actual
                        'user_email' => $userEmail, // Email del usuario
                        'event_type' => $eventType,  // Tipo de evento
                        'description' => $description, // Descripción del evento
                        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A', // URI de la petición
                        'additional_data' => [
                              'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A', // Dirección IP
                              'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A' // Navegador del usuario
                        ]
                  ];

                  // Convierte el log a una cadena JSON para escribirlo
                  $logData = json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                  // Si el archivo no existe, lo crea y escribe la primera línea
                  // Si el archivo existe, se añade al final del archivo
                  if (!file_exists($tempLogsFile)) {
                        file_put_contents($tempLogsFile, $logData . PHP_EOL);
                  } else {
                        file_put_contents($tempLogsFile, $logData . PHP_EOL, FILE_APPEND);
                  }
            }





      // Seguir aquí próxima función...
            // IN: 
            // OUT: 

// ================================================== //
// EJECUCIÓN
// ==================================================

      // Ejecutar la función de generación de perfiles ficticios
      // generateFakeProfiles(40);



      
?>

