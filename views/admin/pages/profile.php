<?php
$session = new SessionManager();
$session->start();
$userName = $session->get('user_name', '');
$username = $session->get('username', '');
$userEmail = $session->get('user_email', '');
$userRole = $session->get('user_role', 'staff');
?>

<main class="min-h-[calc(100vh-64px)] flex items-center justify-center p-8">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                <span class="text-3xl font-bold text-white"><?php echo strtoupper(substr($userName, 0, 1)); ?></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($userName); ?></h1>
            <p class="text-sm text-gray-500 mt-1">@<?php echo htmlspecialchars($username); ?></p>
            <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold <?php echo $userRole === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?> rounded-full"><?php echo ucfirst($userRole); ?></span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Personal Info</h2>
            <div id="nameError" class="hidden mb-3 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>
            <div id="nameSuccess" class="hidden mb-3 p-3 bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg"></div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Full Name</label>
                    <input type="text" id="profileName" value="<?php echo htmlspecialchars($userName); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                    <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($userEmail); ?>" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <button onclick="updateName()" id="nameBtn" class="w-full mt-4 px-4 py-2.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                <span id="nameBtnText">Save Changes</span>
                <svg id="nameSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Change Password</h2>
            <div id="pwError" class="hidden mb-3 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>
            <div id="pwSuccess" class="hidden mb-3 p-3 bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg"></div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Current Password</label>
                    <input type="password" id="currentPassword" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">New Password</label>
                    <input type="password" id="newPassword" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Confirm New Password</label>
                    <input type="password" id="confirmPassword" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <button onclick="updatePassword()" id="pwBtn" class="w-full mt-4 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 rounded-lg transition-colors flex items-center justify-center gap-2">
                <span id="pwBtnText">Update Password</span>
                <svg id="pwSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </button>
        </div>
    </div>
</main>

<script>
(function() {
async function updateName() {
    var name = document.getElementById('profileName').value.trim();
    var email = document.getElementById('profileEmail').value.trim();
    var errorDiv = document.getElementById('nameError');
    var successDiv = document.getElementById('nameSuccess');
    var btn = document.getElementById('nameBtn');
    var btnText = document.getElementById('nameBtnText');
    var spinner = document.getElementById('nameSpinner');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!name) {
        errorDiv.textContent = 'Name is required';
        errorDiv.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Saving...';
    spinner.classList.remove('hidden');

    try {
        var res = await fetch('/api/profile.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_name', name: name, email: email })
        });
        var result = await res.json();
        if (result.success) {
            successDiv.textContent = result.message;
            successDiv.classList.remove('hidden');
        } else {
            errorDiv.textContent = result.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Save Changes';
        spinner.classList.add('hidden');
    }
}

async function updatePassword() {
    var current = document.getElementById('currentPassword').value;
    var newPw = document.getElementById('newPassword').value;
    var confirm = document.getElementById('confirmPassword').value;
    var errorDiv = document.getElementById('pwError');
    var successDiv = document.getElementById('pwSuccess');
    var btn = document.getElementById('pwBtn');
    var btnText = document.getElementById('pwBtnText');
    var spinner = document.getElementById('pwSpinner');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!current || !newPw || !confirm) {
        errorDiv.textContent = 'All password fields are required';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (newPw.length < 6) {
        errorDiv.textContent = 'New password must be at least 6 characters';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (newPw !== confirm) {
        errorDiv.textContent = 'New passwords do not match';
        errorDiv.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btnText.textContent = 'Updating...';
    spinner.classList.remove('hidden');

    try {
        var res = await fetch('/api/profile.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_password', current_password: current, new_password: newPw })
        });
        var result = await res.json();
        if (result.success) {
            successDiv.textContent = result.message;
            successDiv.classList.remove('hidden');
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            errorDiv.textContent = result.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Update Password';
        spinner.classList.add('hidden');
    }
}

window.updateName = updateName;
window.updatePassword = updatePassword;
})();
</script>
