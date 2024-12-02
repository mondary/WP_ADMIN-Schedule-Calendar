// Assurez-vous que le script ne peut être exécuté que dans WordPress
if (!defined('ABSPATH')) exit;

// Ajout du style CSS
function scheduled_posts_calendar_styles_alpha() {
    ?>
    <style>
        .calendar-container {
            max-width: 1200px;
            margin: 20px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        .calendar-nav button {
            padding: 8px 15px;
            border: none;
            background: #2271b1;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .calendar-nav button:hover {
            background: #135e96;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            background: #f0f0f1;
            padding: 15px;
            border-radius: 8px;
        }
        .calendar-day-header {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            background: #2271b1;
            color: white;
            border-radius: 4px;
        }
        .calendar-day {
            min-height: 120px;
            background: white;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .calendar-day.empty {
            background: #f8f9fa;
        }
        .calendar-day .date {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .post-item {
            font-size: 12px;
            margin: 5px 0;
            padding: 5px;
            background: #e9ecef;
            border-radius: 3px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .post-item:hover {
            background: #dee2e6;
        }
        .today {
            border: 2px solid #2271b1;
        }
    </style>
    <?php
}
add_action('admin_head', 'scheduled_posts_calendar_styles_alpha');

// Fonction pour générer le HTML du calendrier
function generate_scheduled_posts_calendar_alpha() {
    ?>
    <div class="wrap">
        <h1>Calendrier des Articles - Version Alpha</h1>
        <div class="calendar-container" data-jetpack-boost="ignore">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <button id="prevMonth" data-jetpack-boost="ignore">&lt; Mois précédent</button>
                    <button id="nextMonth" data-jetpack-boost="ignore">Mois suivant &gt;</button>
                </div>
                <h2 id="currentMonth" data-jetpack-boost="ignore"></h2>
            </div>
            <div class="calendar-grid" id="calendarGrid" data-jetpack-boost="ignore">
                <!-- Le calendrier sera généré ici par JavaScript -->
            </div>
        </div>
    </div>

    <script data-jetpack-boost="ignore">
    document.addEventListener('DOMContentLoaded', function() {
        let currentDate = new Date();
        
        function updateCalendar(date) {
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            
            // Mise à jour du titre du mois
            const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            document.getElementById('currentMonth').textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
            
            // Récupération des articles publiés
            fetch(`<?php echo esc_url(rest_url('wp/v2/posts')); ?>?status=publish&per_page=100&orderby=date&order=desc`, {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(posts => {
                generateCalendarGrid(firstDay, lastDay, posts);
            });
        }

        function generateCalendarGrid(firstDay, lastDay, posts) {
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';
            
            // Ajout des en-têtes des jours
            const dayNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            dayNames.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day-header';
                dayHeader.textContent = day;
                grid.appendChild(dayHeader);
            });
            
            // Ajout des cases vides pour le début du mois
            const emptyDaysStart = (firstDay.getDay() || 7) - 1;
            for (let i = 0; i < emptyDaysStart; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                grid.appendChild(emptyDay);
            }
            
            // Ajout des jours du mois
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-day';
                
                const currentDayDate = new Date(firstDay.getFullYear(), firstDay.getMonth(), day);
                if (currentDayDate.toDateString() === new Date().toDateString()) {
                    dayCell.classList.add('today');
                }
                
                const dateDiv = document.createElement('div');
                dateDiv.className = 'date';
                dateDiv.textContent = day;
                dayCell.appendChild(dateDiv);
                
                // Ajout des articles pour ce jour
                const dayPosts = posts.filter(post => {
                    const postDate = new Date(post.date);
                    return postDate.getDate() === day && 
                           postDate.getMonth() === firstDay.getMonth() &&
                           postDate.getFullYear() === firstDay.getFullYear();
                });
                
                dayPosts.forEach(post => {
                    const postDiv = document.createElement('div');
                    postDiv.className = 'post-item';
                    const postTime = new Date(post.date).toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const postTitle = post.title.rendered.replace(/&amp;/g, '&')
                        .replace(/&lt;/g, '<')
                        .replace(/&gt;/g, '>')
                        .replace(/&quot;/g, '"')
                        .replace(/&#039;/g, "'");
                    postDiv.innerHTML = `${postTime} - ${postTitle}`;
                    postDiv.title = postTitle;
                    postDiv.onclick = () => {
                        window.location.href = `<?php echo admin_url('post.php'); ?>?post=${post.id}&action=edit`;
                    };
                    dayCell.appendChild(postDiv);
                });
                
                grid.appendChild(dayCell);
            }
        }
        
        // Gestionnaires d'événements pour la navigation
        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            updateCalendar(currentDate);
        });
        
        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
            updateCalendar(currentDate);
        });
        
        // Initialisation du calendrier
        updateCalendar(currentDate);
    });
    </script>
    <?php
}

// Ajout du menu dans l'administration WordPress
function add_scheduled_posts_calendar_menu_alpha() {
    add_menu_page(
        'Calendrier des Articles',
        'Calendrier Articles',
        'edit_posts',
        'scheduled-posts-calendar',
        'generate_scheduled_posts_calendar_alpha',
        'dashicons-calendar-alt',
        6
    );
}
add_action('admin_menu', 'add_scheduled_posts_calendar_menu_alpha');
