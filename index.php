<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReserBa Silid</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <nav class="navbar sticky-top">
        <div class="container py-3">
            <a href="index.php" class="navbar-brand logo">ReserBa Silid</a>
            <div class="d-flex gap-3">
                <a href="/src/login.php" class="btn btn-login">Login</a>
                <a href="/src/register.php" class="btn btn-register">Register</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="d-block mb-2">Manage rooms & schedules</span>
                        <span class="d-block hero-title-accent">effortlessly</span>
                    </h1>
                    <p class="hero-description">
                        Track room availability, schedule classes, and handle bookings all in one place. Simple, fast, and built for schools.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="/src/register.php" class="btn btn-register btn-lg">Get Started</a>
                        <a href="/src/login.php" class="btn btn-login btn-lg">Sign In</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer py-4">
        <div class="container">
            <p class="text-center mb-0 small footer-text">© 2025 Reserba Silid. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>