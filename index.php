<?php
require_once 'db.php';

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM urls;");
$stmt->execute();
$counting = $stmt->fetchColumn();
$stmt = null;




// IP des Users abfragen.
// get the user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Definierung standard short code url
// Define Default short code url
$BASE_SHORTURL = "https://short.coffmail.de/";


// URL Abfrage wenn Code gesendet wurde.
// Query URL if a code was send.
if (isset($_GET['code'])) {
    $shortCode = $_GET['code'];

    // Abfrage der Orginal URL aus der Datenbank
    // Look up the original URL in the database
    $stmt = $pdo->prepare("SELECT original_url,count_used FROM urls WHERE short_code = :short_code LIMIT 1");
    $stmt->execute([':short_code' => $shortCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // count_used um 1 erhöhen.
        // Increment the count_used value
        $stmt = $pdo->prepare("UPDATE urls SET count_used = count_used + 1 WHERE short_code = :short_code");
        $stmt->execute([':short_code' => $shortCode]);

        // Zurück zur Orginal URL
        // Back to original url
        if (!preg_match('#^https?://#', $row['original_url'])) {
            $originalUrl = 'https://' . $row['original_url'];
        }
        $stmt = null;

        header("Location: " . $originalUrl);
        exit;
    } else {
        $stmt = null;
        echo "URL nicht gefunden.";
        exit;
    }
}

// Generator für ShortCodes 
// Generator for ShortCodes
function generateShortCode($length = 6) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// Bearbeitung der Create Anfrage
// Handling form creation request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // csrf_token Check (Hidden Form)
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Ungültiges CSRF-Token.");
    }

    $originalUrl = trim($_POST['url']);

    // Abfrage ob der User Sperre hat (Spam schutz)
    // Look if the user got blocked (spam protection)
    $userIp = getUserIP();
    $rateLimitPeriod = 60; // Zeitspanne in Sekunden
    $maxRequests = 60; // Maximale Anzahl der Anfragen in der Zeitspanne | 1:1 Grade

    // Überprüfen, ob die IP-Adresse bereits einen Eintrag in der Rate-Limit-Tabelle hat
    // Check, if the ip adrress already got a entry inside the rate-limit-table.
    $stmt = $pdo->prepare("SELECT last_request, request_count FROM rate_limits WHERE ip_address = :ip_address");
    $stmt->execute([':ip_address' => $userIp]);
    $rateLimitData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rateLimitData) {
        $timeSinceLastRequest = time() - strtotime($rateLimitData['last_request']);

        if ($timeSinceLastRequest < $rateLimitPeriod) {
            if ($rateLimitData['request_count'] >= $maxRequests) {
                die("Du hast die maximale Anzahl von Anfragen erreicht. Bitte versuche es später erneut.");
            } else {

                // Anfragezähler erhöhen
                // request_count +1
                $stmt = $pdo->prepare("UPDATE rate_limits SET request_count = request_count + 1 WHERE ip_address = :ip_address");
                $stmt->execute([':ip_address' => $userIp]);
            }
        } else {
            // Zeitspanne abgelaufen, Zähler zurücksetzen
            // Period ended, reset count
            $stmt = $pdo->prepare("UPDATE rate_limits SET request_count = 1, last_request = NOW() WHERE ip_address = :ip_address");
            $stmt->execute([':ip_address' => $userIp]);
        }
    } else {
        // Neuen Eintrag für die IP-Adresse erstellen
        // Create entry for the ip address
        $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, last_request, request_count) VALUES (:ip_address, NOW(), 1)");
        $stmt->execute([':ip_address' => $userIp]);
    }
    $stmt = null;

    //Prüfe pb URL richtig ist.
    //Check if URL is Valid
    $pattern = '/^(https?:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.[a-zA-Z]{2,}(\/.*)?$/';
    if (preg_match($pattern, $originalUrl)) {
        $shortCode = generateShortCode();


        // Überprüfen ob der short code schon benutzt wird.
        // Check if the short code is already in use
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM urls WHERE short_code = :short_code");
        $stmt->execute([':short_code' => $shortCode]);
        $count = $stmt->fetchColumn();
        while ($count > 0) {
            $shortCode = generateShortCode(); 
            $stmt->execute([':short_code' => $shortCode]);
            $count = $stmt->fetchColumn();
        }
        $stmt = null;

        // Speicher orginal URL, short code und IP in die Datenbank
        // Store the original URL, short code, and IP address in the database
        $stmt = $pdo->prepare("INSERT INTO urls (original_url, short_code, ip_address) VALUES (:original_url, :short_code, :ip_address)");
        $stmt->execute([':original_url' => $originalUrl, ':short_code' => $shortCode, ':ip_address' => $userIp]);
        $stmt = null;
        
        // Erstelle die short URL
        // $shortURL wird beim Reload (ClientSide) angezeigt.
        // Create the short URL
        // $shortURL will be displayed on reload (ClientSide)
        // $shortURL = "https://short.coffmail.de/" + "1A2B3C"
        $_SESSION['shortUrl'] = $BASE_SHORTURL . $shortCode;
        header("Location: index.php?success=1");
    } else {
        $_SESSION['error']  = "Bitte gültigen Link eingeben.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>short @ coffmail.de</title>
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="https://short.coffmail.de"><span style="font-size: larger;">short</span><span style="font-size: small;">.coffmail.de</span></a>
                
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead">
            <div class="container px-4 px-lg-5 d-flex h-100 align-items-center justify-content-center">
                <div class="d-flex justify-content-center">
                    <div class="text-center">
                        <h1 class="mx-auto my-0 text-uppercase">My-URL</h1>
                        <h3 class="text-white-50 mx-auto mt-2 mb-2">A free, fast and simple way to shorten your links.</h3>
                        <h2 class="text-white-50 mx-auto mt-2 mb-2">Current Urls: <span class="countUp-box" data-val="<?php echo $counting ?>">0</span></h2>
                        <?php 
                            if (!empty($_SESSION['error'])) {
                                echo '<p style="color:red;">' . $_SESSION['error'] . '</p>';
                                unset($_SESSION['error']);
                            }
                            if (isset($_GET['success']) && !empty($_SESSION['shortUrl'])) {
                                echo '<p class = "text-white mx-auto mt-2 mb-2">Gekürtze Version: <a href="' . $_SESSION['shortUrl'] . '" target="_blank">' . $_SESSION['shortUrl'] . '</a></p>';
                                unset($_SESSION['shortUrl']); // Clear session data after displaying
                            }

                        ?>
                        <form action="index.php" method="POST">
                            <label for="url" class="text-white form-label">Bitte URL angeben:</label><br>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="text" class = "form-control text-center" id="url" name="url" required><br>
                            <button type="submit" class="btn btn-primary">Erstellen</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <!-- About-->
        <section class="about-section text-center" id="about">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="text-white mb-4">But why?</h2>
                        <p class="text-white-50">
                            I built a URL shortener to challenge my skills and satisfy my curiosity. It was a fun way to turn boredom into a project that explored coding and efficiency. Plus, there's something satisfying about simplifying long links into neat, tidy ones.
                        </p>
                    </div>
                </div>
                <img class="img-fluid" src="assets/img/ipad.png" alt="..." />
            </div>
        </section>
        <!-- Footer-->
        <footer class="footer bg-black small text-center text-white-50"><div class="container px-4 px-lg-2">Copyright &copy; <a href="https://coffmail.de">Coffmail.de</a> 2024</div></footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- CountUp-->
        <script> 
        let allcounters =document.querySelectorAll(".countUp-box");
        let animationTime = 3000;
        allcounters.forEach((counter) => {
            let startCount = 0;
            let count = parseInt(counter.getAttribute("data-val"));
            let timer = Math.floor(animationTime/count);
            let interval = setInterval(function() {
                startCount += 1;
                counter.textContent = startCount;
                if(startCount >= count){
                    clearInterval(interval);
                    counter.textContent = count;
                }
            },timer);
        });
        </script>
    </body>
</html>
