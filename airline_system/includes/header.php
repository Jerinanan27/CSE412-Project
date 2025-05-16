<?php
require_once 'config.php';
require_once 'auth.php';

$page_title = $page_title ?? SITE_NAME;
?>


    

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $page_title ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <?php if (isset($custom_css)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $custom_css ?>">
    <?php endif; ?>

    <!-- Inline Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

       .header-gradient {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 15px 0;
}

.header-inner {
    max-width: 1140px;
    margin: 0 auto;
}


        .navbar-brand {
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #ffffff !important;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #e0e0e0 !important;
        }

        .btn-light,
        .btn-outline-light {
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            background-color: #e9ecef;
        }

        .btn-outline-light:hover {
            background-color: white;
            color: #1e3c72;
        }

        .chatbot-icon {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .chatbot-icon:hover {
            transform: scale(1.1);
        }

        .chatbot-container {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 450px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 15px;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .chatbot-body {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            font-size: 0.95rem;
        }

        .chatbot-footer {
            padding: 15px;
            border-top: 1px solid #eee;
        }

        main.container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
    </style>
</head>

<body>
<header class="header-gradient text-white">
    <div class="header-inner container-lg">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
                <i class="fas fa-plane me-2"></i><?= SITE_NAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/flights/search.php"><i class="fas fa-plane me-1"></i> Flights</a></li>
                    <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/hotels/search.php"><i class="fas fa-hotel me-1"></i> Hotels</a></li>
                    <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/status.php"><i class="fas fa-clock me-1"></i> Flight Status</a></li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/user/dashboard.php"><i class="fas fa-briefcase me-1"></i> My Bookings</a></li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-cog me-1"></i> Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-3">
                            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-3">
                            <a class="btn btn-light btn-sm" href="<?= BASE_URL ?>/auth/login.php">
                                <i class="fas fa-user-circle me-1"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>
</header>


    <div class="container py-4">
      
        <!-- Page content starts here -->
    </div>

    <!-- Optional Chatbot button -->
    <div class="chatbot-icon" onclick="document.querySelector('.chatbot-container').style.display='flex'">
        <i class="fas fa-comments"></i>
    </div>
    <div class="chatbot-container">
        <div class="chatbot-header">Chat Support</div>
        <div class="chatbot-body">Hello! How can I help you today?</div>
        <div class="chatbot-footer">
            <input type="text" class="form-control" placeholder="Type your message...">
        </div>
    </div>

  


