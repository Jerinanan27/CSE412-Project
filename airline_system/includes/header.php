<?php
require_once 'config.php';
require_once 'auth.php';

$page_title = $page_title ?? SITE_NAME;
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Book flights, hotels, and check flight status with <?= SITE_NAME ?>">
    <title><?= $page_title ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #00b4d8;
            --light: #f8f9fa;
            --text: #212529;
            --muted: #6c757d;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--text);
            line-height: 1.6;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .navbar-brand {
            font-size: 1.75rem;
            font-weight: 700;
            color: white !important;
            display: flex;
            align-items: center;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: white !important;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .navbar-nav .nav-link:hover {
            color: var(--accent) !important;
            transform: translateY(-2px);
        }

        .btn-light,
        .btn-outline-light {
            border-radius: 8px;
            padding: 0.5rem 1.25rem;

            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .btn-outline-light:hover {
            background-color: var(--accent);
            color: var(--primary);
            border-color: var(--accent);
        }

        .chatbot-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .chatbot-icon:hover {
            transform: scale(1.15);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .chatbot-container {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 360px;
            height: 480px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
            z-index: 1000;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: slideIn 0.3s ease-in-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .chatbot-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chatbot-close {
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .chatbot-close:hover {
            color: var(--accent);
        }

        .chatbot-body {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            font-size: 0.95rem;
            background: #f9fafb;
        }

        .chatbot-message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            max-width: 80%;
        }

        .chatbot-message.bot {
            background: var(--light);
            margin-left: auto;
            border: 1px solid #e0e0e0;
        }

        .chatbot-message.user {
            background: var(--accent);
            color: white;
            margin-right: auto;
        }

        .chatbot-footer {
            padding: 1rem;
            border-top: 1px solid #eee;
            background: white;
        }

        .chatbot-footer input {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem;
            font-size: 0.95rem;
        }

        main.container {
            padding-top: 3rem;
            padding-bottom: 3rem;
            max-width: 1200px;
        }

        @media (max-width: 768px) {
            .chatbot-container {
                width: 90%;
                height: 70vh;
                bottom: 10px;
                right: 10px;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .navbar-nav .nav-link {
                padding: 0.5rem;
            }
        }
  .btn.btn-primary {
  background-color: #227eda !important;
  color: #fff !important;
  //padding: 5px 5px;
  margin-top: 5px; /* Corrected and added spacing from above content */
  border: none;
  border-radius: 6px;
  font-size: 16px;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn.btn-primary:hover {
  background-color: #005bb5 !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 102, 204, 0.2);
}
    </style>
</head>

<body>
    <header class="header-gradient text-white" role="banner">
        <div class="header-inner container-lg">
            <nav class="navbar navbar-expand-lg navbar-dark" aria-label="Main navigation">
                <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
                    <i class="fas fa-cloud me-2"></i><?= SITE_NAME ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/flights/search.php"><i class="fas fa-plane me-1"></i> Flights</a></li>
                        <li class="nav-item mx-2"><a class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/hotels/search.php"><i class="fas fa-hotel me-1"></i> Hotels</a></li>
                        <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/status.php"><i class="fas fa-clock me-1"></i> Flight Status</a></li>
                        <li class="nav-item mx-2"><a class="nav-link" href="<?= BASE_URL ?>/faq/faq.php"><i class="fas fa-question-circle me-1"></i> FAQ</a></li>
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

    <main class="container py-4" role="main">
        <!-- Page content starts here -->
    </main>

    <!-- Chatbot -->
    <div class="chatbot-icon" onclick="toggleChatbot()" aria-label="Open chat support">
        <i class="fas fa-comments"></i>
    </div>
    <div class="chatbot-container" role="dialog" aria-labelledby="chatbot-header">
        <div class="chatbot-header" id="chatbot-header">
            Chat Support
            <i class="fas fa-times chatbot-close" onclick="toggleChatbot()" aria-label="Close chat"></i>
        </div>
        <div class="chatbot-body">
            <div class="chatbot-message bot">Hello! How can I help you today?</div>
        </div>
        <div class="chatbot-footer">
            <form onsubmit="sendMessage(event)">
                <input type="text" class="form-control" placeholder="Type your message..." aria-label="Chat message input">
                <button type="submit" class="btn btn-outline-primary mt-2">Send</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Chatbot Script -->
    <script>
        function toggleChatbot() {
            const chatbot = document.querySelector('.chatbot-container');
            chatbot.style.display = chatbot.style.display === 'flex' ? 'none' : 'flex';
        }

        function sendMessage(event) {
            event.preventDefault();
            const input = document.querySelector('.chatbot-footer input');
            const message = input.value.trim();
            if (!message) return;

            const chatbotBody = document.querySelector('.chatbot-body');
            const userMessage = document.createElement('div');
            userMessage.className = 'chatbot-message user';
            userMessage.textContent = message;
            chatbotBody.appendChild(userMessage);

            // Simulate bot response (replace with actual API call)
            setTimeout(() => {
                const botMessage = document.createElement('div');
                botMessage.className = 'chatbot-message bot';
                botMessage.textContent = 'Thanks for your message! How else can I assist you?';
                chatbotBody.appendChild(botMessage);
                chatbotBody.scrollTop = chatbotBody.scrollHeight;
            }, 500);

            input.value = '';
            chatbotBody.scrollTop = chatbotBody.scrollHeight;
        }
    </script>
</body>