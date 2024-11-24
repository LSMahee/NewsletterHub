<?php
include 'db.php';

$query = "
    SELECT 
        f.FeedbackID,
        f.Message,
        f.FeedbackDate
    FROM Feedback f
    ORDER BY f.FeedbackDate DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        .feedback-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .feedback-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
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

        .slide-in {
            animation: slideIn 0.5s ease forwards;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .stat-card:hover::after {
            transform: translateX(100%);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-8 slide-in">
            <h1 class="text-3xl font-bold text-gray-900">Feedback Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">View all anonymous feedback</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-8">
            <div class="stat-card bg-white overflow-hidden shadow rounded-lg slide-in" style="animation-delay: 0.1s">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Feedback</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                        <?php echo $result->num_rows; ?>
                    </dd>
                </div>
            </div>

            <div class="stat-card bg-white overflow-hidden shadow rounded-lg slide-in" style="animation-delay: 0.2s">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Recent Feedback (24h)</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                        <?php 
                        $recent = $conn->query("SELECT COUNT(*) as count FROM Feedback WHERE FeedbackDate >= NOW() - INTERVAL 24 HOUR");
                        $recentCount = $recent->fetch_assoc();
                        echo $recentCount['count'];
                        ?>
                        <?php if ($recentCount['count'] > 0): ?>
                            <span class="badge bg-green-100 text-green-800 ml-2">Active</span>
                        <?php endif; ?>
                    </dd>
                </div>
            </div>
        </div>

        <!-- Feedback List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg slide-in" style="animation-delay: 0.3s">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h2 class="text-lg font-medium text-gray-900">Recent Feedback</h2>
            </div>
            <div class="divide-y divide-gray-200">
                <?php $delay = 0.4; while($feedback = $result->fetch_assoc()): ?>
                <div class="feedback-card px-4 py-5 sm:px-6 hover:bg-gray-50 slide-in" 
                     style="animation-delay: <?php echo $delay; ?>s"
                     onclick="showFeedback(<?php echo htmlspecialchars(json_encode($feedback)); ?>)">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center">
                                    <span class="text-white font-medium">A</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Anonymous User</h3>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('F j, Y g:i a', strtotime($feedback['FeedbackDate'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-700">
                        <?php 
                        $message = htmlspecialchars($feedback['Message']);
                        echo strlen($message) > 150 ? substr($message, 0, 150) . '...' : $message; 
                        ?>
                    </div>
                </div>
                <?php $delay += 0.1; endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="feedbackModal">
        <div class="modal-content bg-white w-full max-w-2xl mx-auto my-auto rounded-lg shadow-xl p-6 m-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Feedback Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500" id="modalDate"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-700" id="modalMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showFeedback(feedback) {
            const modal = document.getElementById('feedbackModal');
            const modalDate = document.getElementById('modalDate');
            const modalMessage = document.getElementById('modalMessage');

            modalDate.textContent = new Date(feedback.FeedbackDate).toLocaleString();
            modalMessage.textContent = feedback.Message;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('feedbackModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('feedbackModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>