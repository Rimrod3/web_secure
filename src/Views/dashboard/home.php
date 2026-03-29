<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="text-center">
    <div class="header flex-center-column">
        <!-- Photo de profil -->
        <div class="profile-container" title="Changer la photo de profil">
            <?php if (!empty($current_user_full['photo_profil'])): ?>
                <img src="<?php echo ltrim(htmlspecialchars($current_user_full['photo_profil']), '/'); ?>" alt="Photo de profil" class="profile-img">
            <?php else: ?>
                <div class="profile-placeholder">
                    <!-- Initial removed for cleaner look -->
                </div>
            <?php endif; ?>
            <label for="photo_upload_input" class="profile-edit-icon" title="Changer la photo de profil">📷</label>
        </div>

        <!-- Formulaire d'upload caché -->
        <form id="profile_form" action="upload-profile-picture" method="post" enctype="multipart/form-data" style="display: none;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <input type="file" id="photo_upload_input" name="photo_profil" accept="image/jpeg, image/png, image/webp">
        </form>
        
        <h1 class="mt-20" style="width: 100%;">Tableau de bord</h1>
    </div>

    <?php if ($success_message): ?><div class="success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="error"><?php echo $error_message; ?></div><?php endif; ?>
</div>

<?php if ($user_data->role === 'admin'): ?>
    <div class="section">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Utilisateurs inscrits</h2>
            <button class="btn-main">Gérer les Certifications</button>
        </div>
        <table>
            <thead>
                <tr><th>Pseudo</th><th>Rôle</th><th>État</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($u['pseudo']); ?></strong>
                        <?php if ($u['est_certifie']): ?><span class="badge-blue" title="Certifié">✔</span><?php endif; ?>
                    </td>
                    <td><?php echo $u['role'] === 'admin' ? '<span class="admin-text">Admin</span>' : 'Utilisateur'; ?></td>
                    <td><?php echo $u['est_certifie'] ? 'Certifié' : 'Normal'; ?></td>
                    <td>
                        <?php if ($u['id'] != $user_data->id): ?>
                            <a href="delete-user?delete_user=<?php echo $u['id']; ?>" class="btn-delete-link fs-08 admin-text">Supprimer</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Logs Actions Admin</h3>
        <table class="log-table">
            <thead><tr><th>Date</th><th>Admin</th><th>Action</th><th>Cible</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?php echo $l['date_action']; ?></td>
                    <td><?php echo htmlspecialchars($l['admin_pseudo']); ?></td>
                    <td><strong><?php echo $l['action']; ?></strong></td>
                    <td><?php echo htmlspecialchars($l['cible_pseudo'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Tentatives de Connexion</h3>
        <table class="log-table">
            <thead><tr><th>Date</th><th>Pseudo</th><th>Statut</th><th>IP</th><th>Localisation</th></tr></thead>
            <tbody>
                <?php foreach ($login_logs as $ll): ?>
                <tr>
                    <td><?php echo $ll['date_tentative']; ?></td>
                    <td><?php echo htmlspecialchars($ll['pseudo_tente']); ?></td>
                    <td>
                        <span class="<?php echo $ll['statut'] === 'SUCCESS' ? 'status-success' : 'status-failure'; ?>">
                            <?php echo $ll['statut']; ?>
                        </span>
                    </td>
                    <td><?php echo $ll['adresse_ip']; ?></td>
                    <td><em><?php echo htmlspecialchars($ll['location'] ?? 'Inconnu'); ?></em></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modale Certification -->
    <div id="certifManagerModal" class="modal">
        <div class="modal-content">
            <h2>Certification Manager</h2>
            <form action="certify" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                <div class="form-group">
                    <label for="id_cible">Utilisateur :</label>
                    <select name="id_cible" id="id_cible" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($users as $u): if($u['id'] != $user_data->id): ?>
                            <option value="<?php echo $u['id']; ?>">
                                <?php echo htmlspecialchars($u['pseudo']); ?> 
                                (<?php echo $u['est_certifie'] ? 'Certifié' : 'Non'; ?>)
                            </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="action_type">Action :</label>
                    <select name="action_type" id="action_type" required>
                        <option value="certify">Certifier (Badge Bleu)</option>
                        <option value="decertify">Décertifier</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="admin_password">Mot de passe admin :</label>
                    <input type="password" name="admin_password" id="admin_password" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-confirm">Appliquer</button>
                    <button type="button" class="btn-cancel">Annuler</button>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- Interface Utilisateur -->
    <div class="section">
        <h2>Envoyer une requête</h2>
        <form action="ticket" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            <textarea name="ticket_message" class="ticket-area" placeholder="Message..." required></textarea>
            <br><br>
            <button type="submit" class="btn-main">Envoyer</button>
        </form>
    </div>
    <div class="section">
        <h3>Mes tickets</h3>
        <table>
            <thead><tr><th>Date</th><th>Message</th></tr></thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr><td><?php echo $t['date_envoi']; ?></td><td><?php echo nl2br(htmlspecialchars($t['message'])); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script src="<?php echo BASE_URL; ?>js/accueil.js"></script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
