<template>
    <AppLayout>
        <div class="space-y-6">
            <AppBreadcrumb :items="breadcrumbs" />
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-0">
                <div class="w-full sm:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Manage Profile</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage your profile information and settings.</p>
                </div>
            </div>
            <div class="profile-container">
                <!-- Left Panel - Pilot Profile Summary -->
                
                <div class="profile-summary">
                    <div class="profile-card">
                        <div class="avatar-section">
                            <div class="avatar-container">
                                <div class="avatar bg-gradient-to-r from-indigo-500 to-purple-500 shadow-lg border border-indigo-600">
                                    <span class="avatar-text">{{ user.name.charAt(0) }}</span>
                                    <div class="status-badge">
                                        <Plane size="12" color="white" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="user-info">
                                <h2 class="user-name">{{ user.name }}</h2>
                                <p class="user-rank font-semibold">{{ user.rank }} Rank • {{ user.role }}</p>
                            </div>
                            
                            <div class="status-badges">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="user.status == 1 ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-600 border border-green-400' : 'bg-gradient-to-r from-red-100 to-red-200 text-red-600 border border-red-400'">{{ user.status == 1 ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                        
                        <div class="flight-stats">
                            <div class="stat-item">
                                <span class="stat-label">Flight Hours</span>
                                <span class="stat-value">{{ formatFlightTime(user.flying_hours) }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Member Since</span>
                                <span class="stat-value">{{ user.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Forms -->
                <div class="forms-section">
                    <!-- Personal Information Form -->
                    <div class="form-card">
                        <div class="form-header">
                            <div class="form-title">
                                <User size="20" class="form-icon" />
                                <h2 class="font-bold text-2xl">Personal Information</h2>
                            </div>
                            <p class="form-subtitle">Update your basic profile information</p>
                        </div>
                        
                        <form @submit.prevent="updatePersonalInfo" class="form-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fullName">Full Name</label>
                                    <div class="input-wrapper">
                                        <User size="16" class="input-icon" />
                                        <input 
                                            id="fullName"
                                            v-model="personalInfo.fullName"
                                            type="text"
                                            placeholder="Enter your full name"
                                            class="form-input"
                                        />
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <div class="input-wrapper">
                                        <Mail size="16" class="input-icon" />
                                        <input 
                                            id="email"
                                            v-model="personalInfo.email"
                                            type="email"
                                            placeholder="Enter your email address"
                                            class="form-input"
                                        />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn bg-gradient-to-r from-blue-400 to-blue-500 shadow-lg hover:from-blue-500 hover:to-blue-600 border border-blue-600 text-white font-bold" :disabled="personalInfoLoading">
                                    <Save size="16" class="btn-icon" />
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Security Settings Form -->
                    <div class="form-card">
                        <div class="form-header">
                            <div class="form-title">
                                <Shield size="20" class="form-icon" />
                                <h2 class="font-bold text-2xl">Security Settings</h2>
                            </div>
                            <p class="form-subtitle">Update your password and security preferences</p>
                        </div>
                        
                        <form @submit.prevent="updatePassword" class="form-content">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <div class="input-wrapper">
                                    <Shield size="16" class="input-icon" />
                                    <input 
                                        id="currentPassword"
                                        v-model="securityInfo.currentPassword"
                                        :type="showCurrentPassword ? 'text' : 'password'"
                                        placeholder="Enter current password"
                                        class="form-input"
                                    />
                                    <button 
                                        type="button" 
                                        @click="showCurrentPassword = !showCurrentPassword"
                                        class="password-toggle"
                                    >
                                        <Eye v-if="showCurrentPassword" size="16" />
                                        <EyeOff v-else size="16" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <div class="input-wrapper">
                                    <Shield size="16" class="input-icon" />
                                    <input 
                                        id="newPassword"
                                        v-model="securityInfo.newPassword"
                                        :type="showNewPassword ? 'text' : 'password'"
                                        placeholder="Enter new password"
                                        class="form-input"
                                    />
                                    <button 
                                        type="button" 
                                        @click="showNewPassword = !showNewPassword"
                                        class="password-toggle"
                                    >
                                        <Eye v-if="showNewPassword" size="16" />
                                        <EyeOff v-else size="16" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <div class="input-wrapper">
                                    <Shield size="16" class="input-icon" />
                                    <input 
                                        id="confirmPassword"
                                        v-model="securityInfo.confirmPassword"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        placeholder="Confirm new password"
                                        class="form-input"
                                    />
                                    <button 
                                        type="button" 
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="password-toggle"
                                    >
                                        <Eye v-if="showConfirmPassword" size="16" />
                                        <EyeOff v-else size="16" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn bg-gradient-to-r from-green-400 to-green-500 shadow-lg hover:from-green-500 hover:to-green-600 text-white border border-green-600 font-bold" :disabled="securityInfoLoading">
                                    <svg v-if="securityInfoLoading" width="16" height="16" viewBox="0 0 24 24" fill="none" class="loading-icon">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                                            <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416" repeatCount="indefinite"/>
                                            <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite"/>
                                        </circle>
                                    </svg>
                                    <Shield v-else size="16" class="btn-icon" />
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Discord Integration Form -->
                    <div class="form-card">
                        <div class="form-header">
                            <div class="form-title">
                                <MessageCircle size="20" class="form-icon discord-icon" />
                                <h2 class="font-bold text-2xl">Discord Integration</h2>
                            </div>
                            <p class="form-subtitle">Connect your Discord account for enhanced features</p>
                        </div>
                        
                        <form @submit.prevent="updateDiscordInfo" class="form-content">
                            <div class="form-group">
                                <label for="discordUsername">Discord ID</label>
                                <div class="input-wrapper">
                                    <MessageCircle size="16" class="input-icon" />
                                    <input 
                                        id="discordUsername"
                                        v-model="discordInfo.username"
                                        type="text"
                                        :placeholder="user.discord_id ? user.discord_id : 'Enter your Discord ID'"
                                        class="form-input"
                                    />
                                </div>
                                <p class="input-hint">Enter your Discord ID (e.g., 123456789012345678)</p>
                            </div>
                            
                            <div class="benefits-box bg-gradient-to-r from-purple-100 to-purple-200 p-4 rounded-lg border border-purple-400 shadow-lg">
                                <div class="benefits-header">
                                    <MessageCircle size="16" class="text-purple-600" />
                                    <h3 class="font-bold text-lg text-purple-800">Discord Integration Benefits</h3>
                                </div>
                                <ul class="benefits-list">
                                    <li>• File PIREPs instantly from your Discord server</li>
                                    <li>• Access flight commands through Discord bot (Coming Soon)</li>
                                    <li>• Get real-time events update (Coming Soon)</li>
                                </ul>
                            </div>

                            <div class="benefits-box bg-gradient-to-r from-yellow-100 to-yellow-200 p-4 rounded-lg border border-yellow-400 shadow-lg">
                                <div class="benefits-header">
                                    <MessageCircle size="16" class="text-yellow-600" />
                                    <h3 class="font-bold text-lg text-yellow-800">How to get Discord ID?</h3>
                                </div>
                                <ul class="benefits-list">
                                    <li>• Tap on the cogwheel [] in the bottom left corner of the desktop app to open up your <b>User Settings</b>.</li>
                                    <li>• Then head to <b>Advanced</b>.</li>
                                    <li>• Now, tap on the main toggle next to <b>Developer Mode</b> to enable. A “checkmark” means it’s enabled, while an “x” means it’s disabled.</li>
                                    <li>• Now, navigate back to your server and right click on your name, select <b>Copy UserID</b>.</li>
                                </ul>
                                <p class="mt-4 text-xs font-bold text-gray-500">For more information, please visit <a href="https://support.discord.com/hc/en-us/articles/206346498-Where-can-I-find-my-User-Server-Message-ID-" target="_blank" class="text-blue-600">this page</a>.</p>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn bg-gradient-to-r from-purple-400 to-purple-500 shadow-lg hover:from-purple-500 hover:to-purple-600 text-white border border-purple-600 font-bold" :disabled="discordInfoLoading">
                                    <svg v-if="discordInfoLoading" width="16" height="16" viewBox="0 0 24 24" fill="none" class="loading-icon">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                                            <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416" repeatCount="indefinite"/>
                                            <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite"/>
                                        </circle>
                                    </svg>
                                    <MessageCircle v-else size="16" class="btn-icon" />
                                    Update Discord ID
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Toast Container -->
            <div class="toast-container">
                <RotateToast 
                    v-for="toast in toasts" 
                    :key="toast.id"
                    :type="toast.type"
                    :message="toast.message"
                    :active="toast.active"
                    :overlay="true"
                    @close="removeToast(toast.id)"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
import RotateToast from '@/Components/RotateToast.vue';
import { usePage } from '@inertiajs/vue3';
import rotateDataService from '@/rotate.js';
import { 
    Camera, 
    Plane, 
    User, 
    Mail, 
    Shield, 
    Eye, 
    EyeOff, 
    Save, 
    MessageCircle,
    CheckCircle,
    AlertCircle
} from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user || {};
const breadcrumbs = page.props.breadcrumbs || [];

// Form data
const personalInfo = reactive({
    fullName: user.name,
    email: user.email
});

const securityInfo = reactive({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
});

const discordInfo = reactive({
    username: user.discord_id
});

// Loading states
const personalInfoLoading = ref(false);
const securityInfoLoading = ref(false);
const discordInfoLoading = ref(false);

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Toast management
const toasts = ref([]);
let toastId = 0;

const showToast = (type, message) => {
    const id = ++toastId;
    toasts.value.push({
        id,
        type,
        message,
        active: true
    });
    
    setTimeout(() => {
        removeToast(id);
    }, 5000);
};

const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

// Form submission handlers
const updatePersonalInfo = async () => {
    personalInfoLoading.value = true;
    
    const response = await rotateDataService('/pilots/jxUpdatePersonalInfo', {
        full_name: personalInfo.fullName,
        email: personalInfo.email
    });
    if (response.hasErrors) {
        showToast('error', response.message);
    } else {
        showToast('success', response.message);
        user.name = response.data.name;
        user.email = response.data.email;
    }
    personalInfoLoading.value = false;
};

const formatFlightTime = (totalMinutes) => {
    if (totalMinutes === null || totalMinutes === undefined) {
      return '-';
    }
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h ${minutes}m`;
};

const updatePassword = async () => {
    if (securityInfo.newPassword !== securityInfo.confirmPassword) {
        showToast('error', 'New passwords do not match');
        return;
    }
    
    if (securityInfo.newPassword.length < 8) {
        showToast('error', 'New password must be at least 8 characters long');
        return;
    }
    
    securityInfoLoading.value = true;
    
    const response = await rotateDataService('/pilots/jxUpdatePassword', {
        current_password: securityInfo.currentPassword,
        new_password: securityInfo.newPassword,
        new_password_confirmation: securityInfo.confirmPassword
    });
    if (response.hasErrors) {
        showToast('error', response.message);
        securityInfoLoading.value = false;
    } else {
        showToast('success', response.message);
        securityInfoLoading.value = false;
    }
};

const updateDiscordInfo = async () => {
    if (!discordInfo.username) {
        showToast('error', 'Discord username is required');
        return;
    }
    
    discordInfoLoading.value = true;
    
    const response = await rotateDataService('/pilots/jxUpdateDiscordInfo', {
        discord_id: discordInfo.username
    });
    if (response.hasErrors) {
        showToast('error', response.message);
        discordInfoLoading.value = false;
    } else {
        showToast('success', response.message);
        discordInfoLoading.value = false;
    }
};
</script>

<style scoped>
.profile-container {
    display: flex;
    gap: 32px;
    margin: 0 auto;
    padding: 0;
    width: 100%;
}

.profile-summary {
    flex: 0 0 320px;
}

.profile-header {
    margin-bottom: 24px;
}

.profile-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 8px 0;
}

.profile-subtitle {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.profile-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.avatar-section {
    text-align: center;
    margin-bottom: 24px;
}

.avatar-container {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin: 0 auto;
}

.avatar-text {
    color: white;
    font-size: 2rem;
    font-weight: 600;
}

.camera-overlay {
    position: absolute;
    bottom: -4px;
    right: -4px;
    background: #4f46e5;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.status-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #10b981;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.user-info {
    margin-bottom: 16px;
}

.user-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0 0 4px 0;
}

.user-rank {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.status-badges {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-bottom: 24px;
}

.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.badge.verified {
    background: #3b82f6;
}

.flight-stats {
    border-top: 1px solid #e2e8f0;
    padding-top: 16px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f7fafc;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #718096;
    font-size: 0.875rem;
}

.stat-value {
    color: #1a202c;
    font-weight: 500;
    font-size: 0.875rem;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
}

.status-text {
    color: #1a202c;
    font-weight: 500;
    font-size: 0.875rem;
}

.forms-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.form-header {
    margin-bottom: 24px;
}

.form-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.form-title h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}

.form-icon {
    color: #6366f1;
}

.discord-icon {
    color: #5865f2;
}

.form-subtitle {
    color: #718096;
    font-size: 0.875rem;
    margin: 0;
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 12px;
    color: #9ca3af;
    z-index: 1;
}

.form-input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.password-toggle {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
}

.password-toggle:hover {
    color: #6b7280;
}

.input-hint {
    color: #6b7280;
    font-size: 0.75rem;
    margin: 4px 0 0 0;
}

.benefits-box {
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
}

.benefits-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.benefits-header h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.benefits-list {
    margin: 0;
    padding-left: 20px;
    color: purple;
    font-size: 0.875rem;
    line-height: 1.5;
}

.benefits-list li {
    margin-bottom: 4px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}

.btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover:not(:disabled) {
    background: #059669;
}

.btn-discord {
    background: #5865f2;
    color: white;
}

.btn-discord:hover:not(:disabled) {
    background: #4752c4;
}

.btn-icon {
    width: 16px;
    height: 16px;
}

.loading-icon {
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (max-width: 1024px) {
    .profile-container {
        flex-direction: column;
        gap: 24px;
    }
    
    .profile-summary {
        flex: none;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .profile-container {
        padding: 16px;
    }
    
    .profile-card,
    .form-card {
        padding: 16px;
    }
    
    .profile-title {
        font-size: 1.5rem;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>