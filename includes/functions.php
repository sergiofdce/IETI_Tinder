<?php

// ==================================================
// IMPORTS
// ==================================================
      // Importar Faker
      // require_once '../assets/modules/faker/vendor/autoload.php';
      // use Faker\Factory;

      // Sistema de logs
      $tempLogsFile = 'tmp/logs.txt';

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
            // OUT: /tmp/logs.txt
            function logEvent($eventType, $description, $userEmail) {
                  global $tempLogsFile;

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

                  // Decodifica los logs de JSON a array
                  $logs = [];
                  if (file_exists($tempLogsFile)) {
                        $logs = json_decode(file_get_contents($tempLogsFile), true);
                  }

                  // Agregar el nuevo log al array
                  $logs[] = $log;

                  // Actualiza el archivo de logs
                  file_put_contents($tempLogsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

      // Ordenar logs por usuario y fecha cada 24h
            // IN: processAndSaveLogs()
            // OUT: /logs/YYYY-MM-DD.txt
            function processAndSaveLogs() {
                  global $tempLogsFile;

                  // Leer los logs del archivo temporal
                  $logs = [];
                  if (file_exists($tempLogsFile)) {
                        $logs = json_decode(file_get_contents($tempLogsFile), true);
                  }

                  // Verificar si hay logs para procesar
                  if (empty($logs)) {
                        return;
                  }

                  // Ordenar los logs por usuario (email) y por fecha
                  usort($logs, function ($a, $b) {
                        if ($a['user_email'] === $b['user_email']) {
                              return strtotime($a['timestamp']) <=> strtotime($b['timestamp']);
                        }
                        return strcmp($a['user_email'], $b['user_email']);
                  });

                  // Crear archivo final con formato YYYY-MM-DD.txt
                  $currentDate = date('Y-m-d');
                  $finalLogFile = "../logs/$currentDate.txt";

                  // Generar contenido ordenado por usuarios
                  $output = [];
                  $currentUser = null;

                  foreach ($logs as $log) {
                        if ($currentUser !== $log['user_email']) {
                              $currentUser = $log['user_email'];
                              $output[] = "=== Logs para usuario: $currentUser ===";
                        }
                        $output[] = "[" . $log['timestamp'] . "] " . $log['event_type'] . ": " . $log['description'];
                        if (!empty($log['additional_data'])) {
                              $output[] = "  Datos adicionales: " . json_encode($log['additional_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        }
                  }

                  // Guardar los logs procesados en el archivo final
                  file_put_contents($finalLogFile, implode(PHP_EOL, $output));

                  // Vaciar el archivo temporal
                  file_put_contents($tempLogsFile, '');
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

