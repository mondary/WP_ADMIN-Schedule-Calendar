<?php
/**
 * Changelog:
 * Version 1.0.0j - Ajout des brouillons dans la requête et mise à jour des couleurs.
 */

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
            border-radius: 3px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .post-item.publish {
            background: #d4edda; /* Vert clair pour les articles publiés */
        }
        .post-item.draft {
            background: #ffe5d9; /* Orange clair pour les brouillons */
        }
        .post-item.pending {
            background: #ffeeba; /* Jaune pour les articles en attente */
        }
        .post-item.future {
            background: #cce5ff; /* Bleu clair pour les articles planifiés */
        }
        .today {
            border: 2px solid #2271b1;
        }
        .monthly-stats {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .monthly-stats ul {
            list-style: none;
            padding: 0;
        }
        .monthly-stats li {
            font-size: 14px;
            margin: 5px 0;
        }
        .monthly-stats li span {
            font-weight: bold;
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
                <select id="categoryFilter">
                    <option value="">Toutes les catégories</option>
                    <?php
                    // Récupération des catégories
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="calendar-grid" id="calendarGrid" data-jetpack-boost="ignore">
                <!-- Le calendrier sera généré ici par JavaScript -->
            </div>
            <div class="monthly-stats">
                <h3>Statistiques de l'année</h3>
                <ul>
                    <li><span>Total des articles de l'année :</span> <span id="totalYearPosts"></span></li>
                    <li><span>Total des articles du mois :</span> <span id="totalMonthPosts"></span></li>
                    <li><span>Moyenne des articles par mois :</span> <span id="avgPostsPerMonth"></span></li>
                </ul>
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

            // Récupération de tous les articles avec tous les statuts
            Promise.all([
                // Récupération des articles publiés et programmés
                fetch(`<?php echo esc_url(rest_url('wp/v2/posts')); ?>?per_page=100&status=publish,future&orderby=date&order=desc`, {
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    }
                }).then(response => response.json()),
                // Récupération des brouillons
                fetch(`<?php echo esc_url(rest_url('wp/v2/posts')); ?>?per_page=100&status=draft&orderby=date&order=desc`, {
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    }
                }).then(response => response.json())
            ])
            .then(([publishedPosts, draftPosts]) => {
                console.log('Articles publiés/programmés:', publishedPosts);
                console.log('Brouillons:', draftPosts);
                
                // Fusion des deux types d'articles
                const allPosts = [...publishedPosts, ...draftPosts];
                
                const categoryFilter = document.getElementById('categoryFilter').value;
                const filteredPosts = categoryFilter ? allPosts.filter(post => post.categories.includes(parseInt(categoryFilter))) : allPosts;
                generateCalendarGrid(firstDay, lastDay, filteredPosts);
                updateMonthlyStats(filteredPosts, date.getFullYear(), date.getMonth());
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des articles:', error);
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
                    postDiv.className = 'post-item ' + post.status; // Ajout de la classe pour le statut
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

        function updateMonthlyStats(posts, year, month) {
            // Filtrer les articles de l'année en cours
            const yearlyPosts = posts.filter(post => new Date(post.date).getFullYear() === year);
            const monthlyPosts = posts.filter(post => new Date(post.date).getFullYear() === year && new Date(post.date).getMonth() === month);

            // Calcul de la moyenne des articles par mois
            const avgPostsPerMonth = (yearlyPosts.length > 0) ? 
                (yearlyPosts.length / 12).toFixed(2) : 0;

            document.getElementById('totalYearPosts').textContent = yearlyPosts.length;
            document.getElementById('totalMonthPosts').textContent = monthlyPosts.length;
            document.getElementById('avgPostsPerMonth').textContent = avgPostsPerMonth;
        }

        // Boutons pour naviguer entre les mois
        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            updateCalendar(currentDate);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
            updateCalendar(currentDate);
        });

        // Filtre par catégorie
        document.getElementById('categoryFilter').addEventListener('change', () => {
            updateCalendar(currentDate);
        });

        // Initialisation du calendrier
        updateCalendar(currentDate);
    });
    </script>
    <?php
}

// Ajout de la page du calendrier au menu admin
add_action('admin_menu', function() {
    add_menu_page('Calendrier des Articles', 'Calendrier Articles', 'edit_posts', 'scheduled-posts-calendar', 'generate_scheduled_posts_calendar_alpha', 'dashicons-calendar-alt', 6);
});
?>
