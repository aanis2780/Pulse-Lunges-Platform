<?php
session_start();
include("function.php");

// Vérifie la session
capterConnexion($_SESSION['teacher_id']);



// Connexion à la base
$conn = new mysqli("db", "root", "root_password", "school");
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->begin_transaction();


        // Récupération des données du quiz
        $nom_quiz = $_POST['nom_quiz'] ?? '';
        $teacher_id = $_SESSION['teacher_id'];
        $description = $_POST['description'] ?? '';
        $diagnostic_final = $_POST['diagnostic'] ?? '';
        $tentative_max = $_POST['tentative_max'] ?? 1;


        // Insertion dans la table quiz3
        $stmt = $conn->prepare("INSERT INTO quiz3 (nom_quiz, teacher_id, description, tentative_max, diagnostic_final) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Erreur de préparation quiz3: " . $conn->error);
        }
        $stmt->bind_param("sisis", $nom_quiz, $teacher_id, $description, $tentative_max, $diagnostic_final);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'insertion du quiz: " . $stmt->error);
        }
        
        $quiz3_id = $conn->insert_id;
        $stmt->close();


        // Traitement des thèmes et questions
        if (!isset($_POST['theme']) || !is_array($_POST['theme'])) {
            throw new Exception("Aucun thème n'a été soumis");
        }

        $themes = $_POST['theme'];
        
        foreach ($themes as $i => $theme) {
            if (empty($theme)) {
                continue;
            }


            // Récupération des questions et réponses
            $questions = isset($_POST["question" . $i]) && is_array($_POST["question" . $i]) ? $_POST["question" . $i] : [];
            $reponses = isset($_POST["reponse" . $i]) && is_array($_POST["reponse" . $i]) ? $_POST["reponse" . $i] : [];

            // Nettoyage et validation des données
            $questions_valides = [];
            $reponses_valides = [];

            // On parcourt toutes les questions et réponses
            foreach ($questions as $index => $question) {
                $question = trim($question);
                $reponse = isset($reponses[$index]) ? trim($reponses[$index]) : '';

                // On ne garde que les paires question/réponse non vides
                if (!empty($question) && !empty($reponse)) {
                    $questions_valides[] = $question;
                    $reponses_valides[] = $reponse;
                }
            }

            // Vérification qu'il y a au moins une paire question/réponse
            if (empty($questions_valides) || empty($reponses_valides)) {
                continue;
            }



            // Conversion en JSON
            $questions_json = json_encode($questions_valides, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $reponses_json = json_encode($reponses_valides, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            if ($questions_json === false || $reponses_json === false) {
                continue;
            }

            // Insertion dans qst_quiz_3
            $stmt = $conn->prepare("INSERT INTO qst_quiz_3 (quiz3_id, theme, question, reponse_modele) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Erreur de préparation qst_quiz_3: " . $conn->error);
            }

            $stmt->bind_param("isss", $quiz3_id, $theme, $questions_json, $reponses_json);

            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de l'insertion dans qst_quiz_3: " . $stmt->error);
            }

            $stmt->close();


            // Vérification des données insérées
            $verif = $conn->query("SELECT * FROM qst_quiz_3 WHERE quiz3_id = $quiz3_id AND theme = '" . $conn->real_escape_string($theme) . "'");
            if ($row = $verif->fetch_assoc()) {

            }
        }

        // Validation finale
        $conn->commit();
        header("Location: ../addquiz3.php?etat=true");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../addquiz3.php?etat=false");
        exit();
    }
} else {
    header("Location: ../addquiz3.php?etat=false");
    exit();
}
?>
