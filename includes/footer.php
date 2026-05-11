            </div> <!-- End content-body -->
            
            <footer class="app-footer no-print" style="padding: 30px 40px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-app); margin-top: auto;">
                <div>
                    <p style="font-size: 0.8rem; color: var(--text-main); margin: 0;">&copy; <?php echo date('Y'); ?> <strong><?php echo get_setting('company_name') ?? 'Elite HRMS'; ?></strong>. All rights reserved.</p>
                    <small style="color: var(--text-muted); opacity: 0.6;">Enterprise Suite v2.1.0 • System Secure</small>
                </div>
                <div style="display: flex; gap: 25px;">
                    <a href="#" onclick="openHelpModal()" style="text-decoration: none; font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">Legal</a>
                    <a href="<?php echo BASE_URL; ?>employee/feedback.php" style="text-decoration: none; font-size: 0.8rem; color: var(--primary); font-weight: 800;">Customer Support</a>
                </div>
            </footer>
        </main>
    </div> <!-- End app-container -->
    
    <!-- Command Palette -->
    <div class="cp-overlay" id="cp-overlay"></div>
    <div id="command-palette">
        <div class="cp-header">
            <i class="fas fa-terminal"></i>
            <input type="text" id="cp-input" placeholder="Type a command or search staff..." autocomplete="off">
        </div>
        <div class="cp-results" id="cp-results">
            <!-- Results will be injected here -->
        </div>
        <div class="cp-hint">
            <span><kbd>↑↓</kbd> to navigate • <kbd>Enter</kbd> to select</span>
            <span><kbd>Esc</kbd> to close</span>
        </div>
    </div>

    <!-- Global Help Modal -->
    <div id="helpModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3><i class="fas fa-life-ring" style="color: var(--primary); margin-right: 10px;"></i> System Help Center</h3>
                <button type="button" class="modal-close" onclick="closeHelp()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <div style="display: flex; flex-direction: column; gap: 25px;">
                    <section>
                        <h4 style="font-weight: 800; margin-bottom: 10px; color: var(--primary);">🚀 Getting Started</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted);">Welcome to the Elite HRMS. Use the sidebar to navigate between your personal dashboard, leave requests, and payroll history.</p>
                    </section>
                    
                    <section>
                        <h4 style="font-weight: 800; margin-bottom: 10px; color: var(--success);">💰 Payroll Management</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted);">View and download your monthly payslips under 'My Payroll'. Administrators can process bulk payroll and adjust individual records in the Admin panel.</p>
                    </section>

                    <section>
                        <h4 style="font-weight: 800; margin-bottom: 10px; color: var(--warning);">📅 Leave Requests</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted);">Apply for leave using the 'Request Leave' button. You can track the status of your applications and view manager feedback in real-time.</p>
                    </section>

                    <section>
                        <h4 style="font-weight: 800; margin-bottom: 10px; color: var(--accent);">⌨️ Power User: Command Palette</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted);">Press <kbd style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-family: monospace;">Ctrl + K</kbd> anywhere in the system to open the Global Command Palette for instant navigation.</p>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeHelp()">Got it, thanks!</button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-body" style="padding: 40px 30px;">
                <div style="width: 80px; height: 80px; background: #fef2f2; color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 25px;">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h3 style="font-weight: 800; margin-bottom: 10px;">Confirm Logout</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5;">Are you sure you want to end your current session? You will need to sign in again to access the dashboard.</p>
            </div>
            <div class="modal-footer" style="justify-content: center; padding-bottom: 30px; background: white;">
                <button type="button" class="btn btn-outline" style="padding: 12px 25px;" onclick="closeLogoutModal()">Stay Signed In</button>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-danger" style="padding: 12px 35px; background: var(--danger);">Yes, Sign Out</a>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3><i class="fas fa-user-circle" style="color: var(--primary); margin-right: 10px;"></i> Employee Profile Snapshot</h3>
                <button type="button" class="modal-close" onclick="closeQuickView()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" id="qv-content">
                <!-- Injected via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeQuickView()">Close View</button>
                <a href="#" id="qv-edit-btn" class="btn btn-primary">Edit Full Profile</a>
            </div>
        </div>
    </div>

    <button id="scroll-top" title="Scroll to Top"><i class="fas fa-arrow-up"></i></button>

    <div id="toast-container"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        // Elite Celebration Logic
        function launchCelebration() {
            if (typeof window.confetti !== 'function') return;
            window.confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#6366f1', '#f472b6', '#10b981']
            });
        }

        // Elite Count-Up Animation
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const val = Math.floor(progress * (end - start) + start);
                obj.innerHTML = val.toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // PDF Export Engine
        window.exportToPDF = function(elementId, filename) {
            if (typeof window.html2pdf === 'undefined') return;
            const element = document.getElementById(elementId);
            if (!element) return;
            const opt = {
                margin: 10,
                filename: filename + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        };

        // Global Data Table Sorting
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('sortable')) {
                const th = e.target;
                const table = th.closest('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const index = Array.from(th.parentNode.children).indexOf(th);
                const isAsc = th.classList.contains('sort-asc');
                
                // Reset other headers
                table.querySelectorAll('th').forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                
                rows.sort((a, b) => {
                    const aCol = a.children[index].textContent.trim();
                    const bCol = b.children[index].textContent.trim();
                    return isAsc ? bCol.localeCompare(aCol, undefined, {numeric: true}) : aCol.localeCompare(bCol, undefined, {numeric: true});
                });
                
                th.classList.toggle('sort-asc', !isAsc);
                th.classList.toggle('sort-desc', isAsc);
                
                rows.forEach(row => tbody.appendChild(row));
            }
        });

        // Header UI (works with or without jQuery)
        (function() {
            const body = document.body;

            function lockScroll(locked) {
                body.style.overflow = locked ? 'hidden' : 'auto';
            }

            function setTheme(isDark) {
                body.classList.toggle('dark-mode', !!isDark);
                try {
                    localStorage.setItem('hrms_theme', isDark ? 'dark' : 'light');
                } catch (e) {}

                const btn = document.getElementById('theme-toggle');
                if (btn) {
                    btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-moon', 'fa-sun');
                        icon.classList.add(isDark ? 'fa-sun' : 'fa-moon');
                    }
                }
            }

            function applySavedTheme() {
                let saved = null;
                try {
                    saved = localStorage.getItem('hrms_theme');
                } catch (e) {}

                if (saved === 'dark') return setTheme(true);
                if (saved === 'light') return setTheme(false);

                const prefersDark = !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                setTheme(prefersDark);
            }

            window.hrmsToggleTheme = function() {
                setTheme(!body.classList.contains('dark-mode'));
            };

            // Help Modal (used by inline onclick in header/footer)
            window.openHelpModal = function() {
                const modal = document.getElementById('helpModal');
                if (!modal) return;
                modal.classList.add('active');
                lockScroll(true);
            };

            window.closeHelp = function() {
                const modal = document.getElementById('helpModal');
                if (!modal) return;
                modal.classList.remove('active');
                lockScroll(false);
            };

            // Logout Modal
            window.confirmLogout = function(ev) {
                if (ev && typeof ev.preventDefault === 'function') ev.preventDefault();
                const modal = document.getElementById('logoutModal');
                if (!modal) return false;
                modal.classList.add('active');
                lockScroll(true);
                return false;
            };

            window.closeLogoutModal = function() {
                const modal = document.getElementById('logoutModal');
                if (!modal) return;
                modal.classList.remove('active');
                lockScroll(false);
            };

            // Notifications dropdown
            function toggleDropdown(dropdown, forceState) {
                if (!dropdown) return;
                const next = typeof forceState === 'boolean' ? forceState : !dropdown.classList.contains('active');
                dropdown.classList.toggle('active', next);
                return next;
            }

            const notifBell = document.getElementById('notif-bell');
            const notifDropdown = document.getElementById('notif-dropdown');
            if (notifBell && notifDropdown) {
                notifBell.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (profileDropdown) profileDropdown.classList.remove('active');
                    toggleDropdown(notifDropdown);
                });
            }

            // Profile dropdown
            const profileTrigger = document.getElementById('profile-trigger');
            const profileDropdown = document.getElementById('profile-dropdown');
            if (profileTrigger && profileDropdown) {
                profileTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (notifDropdown) notifDropdown.classList.remove('active');
                    toggleDropdown(profileDropdown);
                });
            }

            // Secure logout links (now real hrefs with JS interception)
            document.querySelectorAll('.js-confirm-logout').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    window.confirmLogout(e);
                });
            });

            // Theme toggle button
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.hrmsToggleTheme();
                });
            }

            // Click-outside + ESC handling
            document.addEventListener('click', function(e) {
                // Close dropdowns if clicking outside
                const clickOnNotif = notifBell && notifBell.contains(e.target);
                const clickOnNotifDropdown = notifDropdown && notifDropdown.contains(e.target);
                if (notifDropdown && !clickOnNotif && !clickOnNotifDropdown) {
                    notifDropdown.classList.remove('active');
                }
                
                const clickOnProfile = profileTrigger && profileTrigger.contains(e.target);
                const clickOnProfileDropdown = profileDropdown && profileDropdown.contains(e.target);
                if (profileDropdown && !clickOnProfile && !clickOnProfileDropdown) {
                    profileDropdown.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Escape') return;
                if (notifDropdown) notifDropdown.classList.remove('active');
                if (profileDropdown) profileDropdown.classList.remove('active');
                window.closeHelp();
                window.closeLogoutModal();
            });

            // Backdrop click-to-close for modals
            const helpModal = document.getElementById('helpModal');
            if (helpModal) {
                helpModal.addEventListener('click', function(e) {
                    if (e.target === helpModal) window.closeHelp();
                });
            }
            const logoutModal = document.getElementById('logoutModal');
            if (logoutModal) {
                logoutModal.addEventListener('click', function(e) {
                    if (e.target === logoutModal) window.closeLogoutModal();
                });
            }

            applySavedTheme();
        })();

        (function() {
            const hasJQuery = typeof window.jQuery !== 'undefined' && typeof window.$ === 'function';
            if (hasJQuery) {
        // Command Palette Logic
        const cp = $('#command-palette');
        const cpInput = $('#cp-input');
        const cpResults = $('#cp-results');
        const cpOverlay = $('#cp-overlay');

        const commands = [
            { name: 'Dashboard', icon: 'fa-gauge', url: '<?php echo BASE_URL . ($is_admin ? "admin" : "employee"); ?>/dashboard.php' },
            <?php if ($is_admin): ?>
            { name: 'Employees List', icon: 'fa-users', url: '<?php echo BASE_URL; ?>admin/employees.php' },
            { name: 'Run Payroll', icon: 'fa-calculator', url: '<?php echo BASE_URL; ?>admin/payroll.php' },
            { name: 'Manage Departments', icon: 'fa-building', url: '<?php echo BASE_URL; ?>admin/departments.php' },
            { name: 'System Settings', icon: 'fa-cogs', url: '<?php echo BASE_URL; ?>admin/settings.php' },
            <?php endif; ?>
            { name: 'My Profile', icon: 'fa-user-circle', url: '<?php echo BASE_URL; ?>employee/profile.php' },
            { name: 'Toggle Theme', icon: 'fa-adjust', action: () => { if (window.hrmsToggleTheme) window.hrmsToggleTheme(); } },
            { name: 'Logout', icon: 'fa-sign-out-alt', url: '<?php echo BASE_URL; ?>logout.php' }
        ];

        function openPalette() {
            cp.addClass('active');
            cpOverlay.addClass('active');
            cpInput.val('').focus();
            renderResults(commands);
        }

        function closePalette() {
            cp.removeClass('active');
            cpOverlay.removeClass('active');
        }

        function renderResults(list) {
            let html = '';
            list.forEach((cmd, i) => {
                html += `
                    <div class="cp-item ${i === 0 ? 'selected' : ''}" data-url="${cmd.url || ''}" data-index="${i}">
                        <i class="fas ${cmd.icon}"></i>
                        <span>${cmd.name}</span>
                    </div>
                `;
            });
            cpResults.html(html);
        }

        $(document).keydown(function(e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                openPalette();
            }
            if (e.key === 'Escape') closePalette();
        });

        cpOverlay.on('click', closePalette);

        cpInput.on('input', function() {
            const q = $(this).val().toLowerCase();
            const filtered = commands.filter(c => c.name.toLowerCase().includes(q));
            renderResults(filtered);
        });

        $(document).on('click', '.cp-item', function() {
            const index = $(this).data('index');
            const q = cpInput.val().toLowerCase();
            const filtered = commands.filter(c => c.name.toLowerCase().includes(q));
            const cmd = filtered[index];

            if (cmd.url) window.location.href = cmd.url;
            if (cmd.action) {
                cmd.action();
                closePalette();
            }
        });
        function showToast(title, message, type = 'success') {
            const id = 'toast-' + Math.random().toString(36).substr(2, 9);
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const toastHtml = `
                <div id="${id}" class="toast toast-${type}" onclick="this.style.animation='fadeOut 0.3s forwards'; setTimeout(() => this.remove(), 300);">
                    <div class="toast-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="toast-content">
                        <h4>${title}</h4>
                        <p>${message}</p>
                    </div>
                </div>
            `;
            
            $('#toast-container').append(toastHtml);
            setTimeout(() => {
                $(`#${id}`).css('animation', 'fadeOut 0.3s forwards');
                setTimeout(() => $(`#${id}`).remove(), 300);
            }, 5000);
        }

        // Page Progress Loader
        window.onload = () => {
            const bar = document.getElementById('nprogress-bar');
            if (bar) {
                bar.style.width = '100%';
                setTimeout(() => {
                    bar.style.opacity = '0';
                    setTimeout(() => bar.style.display = 'none', 400);
                }, 500);
            }
        };

        window.openHelpModal = function() {
            $('#helpModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }

        window.closeHelp = function() {
            $('#helpModal').removeClass('active');
            $('body').css('overflow', 'auto');
        }

        window.openQuickView = function(emp) {
            const content = `
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 100px; height: 100px; border-radius: 20px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 15px; box-shadow: var(--shadow-float);">
                        ${emp.full_name.charAt(0).toUpperCase()}
                    </div>
                    <h4 style="margin: 0; font-weight: 800; font-size: 1.2rem;">${emp.full_name}</h4>
                    <p style="color: var(--primary); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; margin-top: 5px;">${emp.position}</p>
                </div>

                <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                        <small style="display: block; color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Employee ID</small>
                        <strong style="font-size: 0.9rem;">${emp.employee_id}</strong>
                    </div>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                        <small style="display: block; color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Department</small>
                        <span class="badge badge-info" style="font-size: 0.65rem;">${emp.department_name || 'General'}</span>
                    </div>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                        <small style="display: block; color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Hire Date</small>
                        <strong style="font-size: 0.9rem;">${new Date(emp.hire_date).toLocaleDateString('en-US', {month: 'long', day: 'numeric', year: 'numeric'})}</strong>
                    </div>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                        <small style="display: block; color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Leave Balance</small>
                        <strong style="font-size: 0.9rem; color: var(--success);">${emp.leave_balance} Days</strong>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-envelope" style="color: var(--primary);"></i>
                    <div style="flex: 1;">
                        <small style="display: block; color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Email Address</small>
                        <strong style="font-size: 0.85rem;">${emp.email}</strong>
                    </div>
                </div>
            `;
            
            $('#qv-content').html(content);
            $('#qv-edit-btn').attr('href', `employee_edit.php?id=${emp.id}`);
            $('#quickViewModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }

        window.closeQuickView = function() {
            $('#quickViewModal').removeClass('active');
            $('body').css('overflow', 'auto');
        }

        window.confirmLogout = function() {
            $('#logoutModal').addClass('active');
            $('body').css('overflow', 'hidden');
        }

        window.closeLogoutModal = function() {
            $('#logoutModal').removeClass('active');
            $('body').css('overflow', 'auto');
        }

$(document).ready(function() {
    // Mobile-first: start with sidebar collapsed
    if (window.innerWidth <= 1024) {
        $('body').removeClass('sidebar-open');
        $('body').css('overflow', 'auto');
    }

    // Live Clock Logic
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour12: false });
        $('#live-clock').text(timeStr);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Parallax Blobs
    $(document).on('mousemove', function(e) {
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        $('.blob-1').css('transform', `translate(${x * 50}px, ${y * 50}px)`);
        $('.blob-2').css('transform', `translate(-${x * 30}px, -${y * 30}px)`);
    });

            // Initial progress
            $('#nprogress-bar').css('width', '30%');

            // Scroll to Top Logic
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('#scroll-top').fadeIn().css('display', 'flex');
                } else {
                    $('#scroll-top').fadeOut();
                }
            });

            $('#scroll-top').on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 500);
                return false;
            });

            // Button Loading Logic
            $('form').on('submit', function() {
                const btn = $(this).find('button[type="submit"]');
                if (btn.length && !btn.hasClass('no-loader')) {
                    btn.addClass('btn-loading');
                }
            });

            // Password Toggle Logic
            $(document).on('click', '.pass-toggle', function() {
                const container = $(this).closest('.form-icon-group');
                const input = container.find('input');
                const icon = $(this);
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Dynamic Greetings
            const hours = new Date().getHours();
            let greeting = 'Good evening';
            if (hours < 12) greeting = 'Good morning';
            else if (hours < 18) greeting = 'Good afternoon';
            
            $('.dynamic-greeting').text(greeting);

            // Sidebar Toggle
            $('#sidebar-toggle').on('click', function() {
                $('body').toggleClass('sidebar-open');
                if ($('body').hasClass('sidebar-open') && window.innerWidth <= 1024) {
                    $('body').css('overflow', 'hidden');
                } else {
                    $('body').css('overflow', 'auto');
                }
            });

            // Close sidebar on mobile when clicking content or overlay
            $('.main-content').on('click', function() {
                if (window.innerWidth <= 1024 && $('body').hasClass('sidebar-open')) {
                    $('body').removeClass('sidebar-open');
                    $('body').css('overflow', 'auto');
                }
            });

             // Active Link handling (fallback)
             const currentPath = window.location.pathname;
             $('.nav-menu a').each(function() {
                 const href = $(this).attr('href') || '';
                 if (href.includes(currentPath)) {
                     $(this).addClass('active');
                 }
             });

            $('#helpModal').on('click', function(e) {
                if (e.target === this) closeHelp();
            });

            $('#quickViewModal').on('click', function(e) {
                if (e.target === this) closeQuickView();
            });

            $('#logoutModal').on('click', function(e) {
                if (e.target === this) closeLogoutModal();
            });

            // Dynamic Favicon Badge
            function updateFaviconBadge(count) {
                const favicon = document.querySelector('link[rel="icon"]');
                if (!favicon) return;
                
                const canvas = document.createElement('canvas');
                canvas.width = 32;
                canvas.height = 32;
                const ctx = canvas.getContext('2d');
                
                const img = new Image();
                img.src = favicon.href;
                img.onload = function() {
                    ctx.drawImage(img, 0, 0, 32, 32);
                    if (count > 0) {
                        ctx.beginPath();
                        ctx.arc(24, 8, 8, 0, 2 * Math.PI);
                        ctx.fillStyle = '#ef4444';
                        ctx.fill();
                        ctx.fillStyle = 'white';
                        ctx.font = 'bold 12px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText(count > 9 ? '9+' : count, 24, 12);
                    }
                    favicon.href = canvas.toDataURL('image/png');
                };
            }
            
            <?php if(isset($unread_count) && $unread_count > 0): ?>
                updateFaviconBadge(<?php echo $unread_count; ?>);
            <?php endif; ?>

            // Handle PHP success/error messages as toasts
            <?php if (isset($success) && !empty($success)): ?>
                showToast('Success', '<?php echo addslashes($success); ?>', 'success');
            <?php endif; ?>
            
            <?php if (isset($error) && !empty($error)): ?>
                showToast('Error', '<?php echo addslashes($error); ?>', 'danger');
            <?php endif; ?>

            // Live Image Preview for Profile
            $('#profile_pic_input').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreviewContainer').html(`<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`);
                        $('#imagePreviewContainer').css('border-style', 'solid');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Global Employee Search
            const gSearchInput = $('#globalEmployeeSearch');
            const gResultsBox = $('#globalSearchResults');

            if (gSearchInput.length) {
                gSearchInput.on('input', function() {
                    const query = $(this).val();
                    if (query.length < 2) {
                        gResultsBox.hide();
                        return;
                    }

                    $.ajax({
                        url: '<?php echo BASE_URL; ?>admin/ajax_search.php',
                        data: { q: query },
                        success: function(data) {
                            if (data.length === 0) {
                                gResultsBox.html('<p style="text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 10px 0;">No staff found</p>').show();
                                return;
                            }

                            let html = '';
                            data.forEach(emp => {
                                const picHtml = emp.pic 
                                    ? `<img src="${emp.pic}" style="width: 30px; height: 30px; border-radius: 6px; object-fit: cover;">`
                                    : `<div style="width: 30px; height: 30px; border-radius: 6px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800;">${emp.initials}</div>`;

                                html += `
                                    <a href="<?php echo BASE_URL; ?>admin/employee_edit.php?id=${emp.id}" style="display: flex; align-items: center; gap: 12px; padding: 10px; text-decoration: none; border-radius: 10px; transition: background 0.2s;">
                                        ${picHtml}
                                        <div style="line-height: 1.2;">
                                            <strong style="display: block; font-size: 0.85rem; color: var(--text-main);">${emp.name}</strong>
                                            <small style="color: var(--text-muted); font-size: 0.7rem;">${emp.code} • ${emp.position}</small>
                                        </div>
                                    </a>
                                `;
                            });
                            gResultsBox.html(html).show();
                            
                            // Hover effect
                            gResultsBox.find('a').hover(
                                function() { $(this).css('background', '#f1f5f9'); },
                                function() { $(this).css('background', 'transparent'); }
                            );
                        }
                    });
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.top-search-wrapper').length) {
                        gResultsBox.hide();
                    }
                });
            }
        });
            } // end if (hasJQuery)
        })();
    </script>
</body>
</html>
<?php
// Close DB connection if needed, but it's usually handled by the script
?>
