<?php
/**
 * Changelog:
 * v1.0 "Le Petit Calendrier" - Premier jet du calendrier
 * v1.1 "Le Chasseur d'Articles" - Récupération de tous les articles avec pagination
 * v1.2 "Le Débuggeur Fou" - Ajout de la gestion des erreurs
 * v1.3 "Le Filtreur Magique" - Ajout du filtre par catégories
 * v1.4 "L'Explorateur Temporel" - Modification pour inclure tous les articles
 * v1.5 "Le Prophète" - Correction pour les articles programmés
 * v1.6 "Retour vers le Futur" - Inclusion des articles des jours à venir
 * v1.7 "Le Maître du Temps" - Amélioration de l'affichage des articles programmés
 * v1.8 "Le Grand Rassembleur" - Inclusion de tous les statuts d'articles
 * v1.9 "Le Brouillon Farceur" - Ajout des brouillons et mise à jour des couleurs
 * v2.0 "L'Arc-en-Ciel" - Harmonisation des couleurs entre les vues
 * v2.1 "Le Minimaliste" - Simplification du menu et historique complet
 * v2.2 "L'Épurateur" - Simplification des titres
 * v2.3 "Le Jongleur" - Ajout du drag & drop et de la recherche rapide
 * v2.4 "L'Ergonome" - Réorganisation de l'affichage des articles avec actions et grip
 * v2.5 "L'Esthète" - Refonte des tuiles articles et amélioration du drag & drop
 */

// Assurez-vous que le script ne peut être exécuté que dans WordPress
if (!defined('ABSPATH')) exit;

// Ajout des scripts nécessaires
function add_calendar_scripts() {
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
}
add_action('admin_enqueue_scripts', 'add_calendar_scripts');

// Ajout du style CSS pour le calendrier et la liste d'articles
function scheduled_posts_calendar_styles_alpha() {
    $screen = get_current_screen();
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
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .post-item.publish,
        .status-publish {
            background: #d4edda !important; /* Vert clair pour les articles publiés */
        }
        .post-item.draft,
        .status-draft {
            background: #ffe5d9 !important; /* Orange clair pour les brouillons */
        }
        .post-item.pending,
        .status-pending {
            background: #ffeeba !important; /* Jaune pour les articles en attente */
        }
        .post-item.future,
        .status-future {
            background: #cce5ff !important; /* Bleu clair pour les articles planifiés */
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

        /* Styles spécifiques pour la liste d'articles */
        .wp-list-table tr.status-publish {
            background: #d4edda !important;
        }
        .wp-list-table tr.status-draft {
            background: #ffe5d9 !important;
        }
        .wp-list-table tr.status-pending {
            background: #ffeeba !important;
        }
        .wp-list-table tr.status-future {
            background: #cce5ff !important;
        }

        /* Hover states pour la liste d'articles */
        .wp-list-table tr.status-publish:hover {
            background: #c3e6cb !important;
        }
        .wp-list-table tr.status-draft:hover {
            background: #ffd5c2 !important;
        }
        .wp-list-table tr.status-pending:hover {
            background: #ffe7a0 !important;
        }
        .wp-list-table tr.status-future:hover {
            background: #b8daff !important;
        }

        /* S'assurer que le texte reste lisible */
        .wp-list-table tr td {
            color: #000 !important;
        }

        /* Styles pour la barre de recherche */
        .calendar-search {
            margin: 10px 0;
            padding: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .calendar-search input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Styles pour le drag & drop */
        .post-item.dragging {
            opacity: 0.5;
            cursor: move;
        }
        .calendar-day.droppable-hover {
            background: #f0f7ff;
        }

        .post-time {
            font-size: 10px;
            color: #666;
            position: absolute;
            top: 2px;
            right: 5px;
        }

        .post-grip {
            cursor: move;
            padding-right: 8px;
            color: #999;
        }

        .post-title {
            flex-grow: 1;
            margin-right: 5px;
            padding-right: 45px;
        }

        .post-actions {
            display: none;
            position: absolute;
            right: 5px;
            bottom: 2px;
        }

        .post-item:hover .post-actions {
            display: block;
        }

        .post-actions a {
            text-decoration: none;
            color: #666;
            margin-left: 8px;
            font-size: 14px;
        }

        .post-actions a:hover {
            color: #2271b1;
        }

        /* Ajout des styles pour le dashicons */
        .post-item .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
            line-height: 14px;
        }

        .post-title {
            font-weight: 500;
            line-height: 1.4;
            padding: 0 4px;
        }

        .post-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 6px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .post-grip {
            cursor: move;
            color: #999;
            padding: 2px;
        }

        .post-time {
            font-size: 11px;
            color: #666;
            flex: 1;
            text-align: center;
            margin: 0 8px;
        }

        .post-actions {
            display: flex;
            gap: 8px;
        }

        .post-actions a {
            text-decoration: none;
            color: #666;
            display: flex;
            align-items: center;
            padding: 2px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .post-actions a:hover {
            color: #2271b1;
            background: rgba(0,0,0,0.05);
        }

        .post-item .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
            line-height: 14px;
        }

        /* Styles pour le drag & drop */
        .post-item.ui-draggable-dragging {
            transform: scale(0.95);
            opacity: 0.8;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .calendar-day.droppable-hover {
            background: #f0f7ff;
            box-shadow: inset 0 0 0 2px #2271b1;
        }
    </style>
    <?php
}

// Ajouter les styles à l'administration
add_action('admin_head', 'scheduled_posts_calendar_styles_alpha');

// Fonction pour générer le HTML du calendrier
function generate_scheduled_posts_calendar_alpha() {
    ?>
    <div class="wrap">
        <h1>Calendrier</h1>
        <div class="calendar-container" data-jetpack-boost="ignore">
            <div class="calendar-search">
                <input type="text" id="searchPosts" placeholder="Rechercher des articles...">
            </div>
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
                    postDiv.className = 'post-item ' + post.status;
                    postDiv.setAttribute('data-post-id', post.id);

                    const postTime = new Date(post.date).toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const postTitle = post.title.rendered.replace(/&amp;/g, '&')
                        .replace(/&lt;/g, '<')
                        .replace(/&gt;/g, '>')
                        .replace(/&quot;/g, '"')
                        .replace(/&#039;/g, "'");

                    postDiv.innerHTML = `
                        <div class="post-title">${postTitle}</div>
                        <div class="post-footer">
                            <span class="post-grip dashicons dashicons-menu"></span>
                            <span class="post-time">${postTime}</span>
                            <div class="post-actions">
                                <a href="${post.link}" target="_blank" title="Voir l'article">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                                <a href="<?php echo admin_url('post.php'); ?>?post=${post.id}&action=edit" title="Modifier l'article">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                            </div>
                        </div>
                    `;

                    dayCell.appendChild(postDiv);
                });

                grid.appendChild(dayCell);
            }

            // Initialiser le drag & drop après la génération du calendrier
            initDragAndDrop();
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

        // Fonction de recherche
        document.getElementById('searchPosts').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const postItems = document.querySelectorAll('.post-item');
            
            postItems.forEach(item => {
                const title = item.textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Initialisation du drag & drop
        function initDragAndDrop() {
            $('.post-item').draggable({
                handle: '.post-grip',
                revert: 'invalid',
                zIndex: 100,
                cursor: 'move',
                cursorAt: { top: 15, left: 15 },
                helper: function() {
                    const clone = $(this).clone().css({
                        width: $(this).width(),
                        height: 'auto'
                    });
                    return clone;
                },
                start: function(event, ui) {
                    $(this).addClass('dragging');
                },
                stop: function(event, ui) {
                    $(this).removeClass('dragging');
                }
            });

            $('.calendar-day').droppable({
                accept: '.post-item',
                hoverClass: 'droppable-hover',
                drop: function(event, ui) {
                    const postId = ui.draggable.data('post-id');
                    const newDate = $(this).data('date');
                    const oldDate = new Date(ui.draggable.closest('.calendar-day').data('date'));
                    const newDateTime = new Date(newDate);
                    
                    // Conserver l'heure de l'article original
                    newDateTime.setHours(oldDate.getHours());
                    newDateTime.setMinutes(oldDate.getMinutes());

                    // Mise à jour via l'API REST
                    fetch(`<?php echo esc_url(rest_url('wp/v2/posts/')); ?>${postId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        body: JSON.stringify({
                            date: newDateTime.toISOString()
                        })
                    })
                    .then(response => response.json())
                    .then(post => {
                        updateCalendar(currentDate);
                    })
                    .catch(error => {
                        console.error('Erreur lors de la mise à jour de la date:', error);
                    });
                }
            });
        }

        // Initialisation du calendrier
        updateCalendar(currentDate);
    });
    </script>
    <?php
}

// Ajout de la page du calendrier au menu admin
add_action('admin_menu', function() {
    add_menu_page('Calendrier des Articles', 'Calendrier', 'edit_posts', 'scheduled-posts-calendar', 'generate_scheduled_posts_calendar_alpha', 'dashicons-calendar-alt', 6);
});
?>
