<script>
    // --- Template Modal Script ---
    function openTemplateModal(userId, userName, allowedTemplates, defaultTemplate) {
        document.getElementById('modalUserName').innerText = userName;
        document.getElementById('templateForm').action = `/admin/users/${userId}/templates`;
        
        document.querySelectorAll('input[name="templates[]"]').forEach(el => el.checked = false);
        if (Array.isArray(allowedTemplates)) {
            allowedTemplates.forEach(val => {
                const checkbox = document.querySelector(`input[name="templates[]"][value="${val}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        const select = document.querySelector('select[name="default_template"]');
        if(select) select.value = defaultTemplate;

        const modal = document.getElementById('templateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeTemplateModal() {
        document.getElementById('templateModal').classList.add('hidden');
        document.getElementById('templateModal').classList.remove('flex');
    }
    
    // --- Limit Modal Script ---
    function toggleLimitInputs(type) {
        if (type === 'monthly') {
            document.getElementById('monthlyLimitWrapper').classList.remove('hidden');
            document.getElementById('dailyLimitWrapper').classList.add('hidden');
        } else {
            document.getElementById('monthlyLimitWrapper').classList.add('hidden');
            document.getElementById('dailyLimitWrapper').classList.remove('hidden');
        }
    }

    function openLimitModal(userId, userName, currentLimit, limitType, monthlyLimit) {
        document.getElementById('limitModalUserName').innerText = userName;
        document.getElementById('limitInput').value = currentLimit || 10;
        document.getElementById('monthlyLimitInput').value = monthlyLimit || (currentLimit ? currentLimit * 30 : 300);
        document.getElementById('limitForm').action = `/admin/users/${userId}/limit`;
        
        const type = limitType || 'daily';
        if (type === 'monthly') {
            document.getElementById('limitTypeMonthly').checked = true;
            toggleLimitInputs('monthly');
        } else {
            document.getElementById('limitTypeDaily').checked = true;
            toggleLimitInputs('daily');
        }

        const modal = document.getElementById('limitModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeLimitModal() {
        document.getElementById('limitModal').classList.add('hidden');
        document.getElementById('limitModal').classList.remove('flex');
    }
    
    // --- Source Modal Script ---
    function openSourceModal(userId, userName, assignedWebsites) {
        document.getElementById('sourceModalUserName').innerText = userName;
        document.getElementById('sourceForm').action = `/admin/users/${userId}/websites`;
        
        const checkboxes = document.querySelectorAll('#sourceForm input[name="websites[]"]');
        checkboxes.forEach(el => el.checked = false);
        if (Array.isArray(assignedWebsites)) {
            assignedWebsites.forEach(id => {
                const checkbox = document.querySelector(`#sourceForm input[value="${id}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        const modal = document.getElementById('sourceModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeSourceModal() {
        document.getElementById('sourceModal').classList.add('hidden');
        document.getElementById('sourceModal').classList.remove('flex');
    }

    // --- Scraper Modal Script ---
    function openScraperModal(userId, userName, currentMethod, autoCleanDays, cooldownMinutes, concurrentLimit) {
        document.getElementById('scraperUserName').innerText = userName;
        document.getElementById('scraperForm').action = `/admin/users/${userId}/scraper`;
        document.getElementById('scraperInput').value = currentMethod || "";
        document.getElementById('autoCleanDaysInput').value = autoCleanDays || 7;
        document.getElementById('cooldownMinutesInput').value = cooldownMinutes || 5;
        document.getElementById('concurrentLimitInput').value = concurrentLimit || 3;
        
        const modal = document.getElementById('scraperModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeScraperModal() {
        document.getElementById('scraperModal').classList.add('hidden');
        document.getElementById('scraperModal').classList.remove('flex');
    }
    
    // --- Create User Modal ---
    function openCreateUserModal() {
        const modal = document.getElementById('createUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
        document.getElementById('createUserModal').classList.remove('flex');
    }

    // --- Edit User Modal (Updated for Subscription, Plan, Limits & Expiry) ---
    function openEditUserModal(userId, name, email, staffLimit, planId, expireDate, status, credits, dailyLimit, limitType, monthlyLimit) {
        document.getElementById('editName').value = name || '';
        document.getElementById('editEmail').value = email || '';
        document.getElementById('editStaffLimit').value = (staffLimit !== undefined && staffLimit !== null) ? staffLimit : 0;
        
        if (document.getElementById('editPricingPlanId')) {
            document.getElementById('editPricingPlanId').value = planId || '';
        }
        if (document.getElementById('editExpireDate')) {
            document.getElementById('editExpireDate').value = expireDate || '';
        }
        if (document.getElementById('editSubscriptionStatus')) {
            document.getElementById('editSubscriptionStatus').value = status || 'active';
        }
        if (document.getElementById('editCredits')) {
            document.getElementById('editCredits').value = (credits !== undefined && credits !== null) ? credits : 10;
        }
        if (document.getElementById('editPostLimitType')) {
            document.getElementById('editPostLimitType').value = limitType || 'daily';
        }
        if (document.getElementById('editDailyPostLimit')) {
            document.getElementById('editDailyPostLimit').value = (dailyLimit !== undefined && dailyLimit !== null) ? dailyLimit : 10;
        }
        if (document.getElementById('editMonthlyPostLimit')) {
            document.getElementById('editMonthlyPostLimit').value = (monthlyLimit !== undefined && monthlyLimit !== null) ? monthlyLimit : '';
        }
        
        document.getElementById('editUserForm').action = `/admin/users/${userId}/update`;
        
        const modal = document.getElementById('editUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
        document.getElementById('editUserModal').classList.remove('flex');
    }
    
    // --- Permission Modal ---
    function openPermissionModal(userId, userName, userPerms) {
        document.getElementById('permUserName').innerText = userName;
        document.getElementById('permissionForm').action = `/admin/users/${userId}/permissions`;
        
        const checkboxes = document.querySelectorAll('#permissionForm input[name="permissions[]"]');
        checkboxes.forEach(cb => {
            cb.checked = Array.isArray(userPerms) && userPerms.includes(cb.value);
        });

        const modal = document.getElementById('permissionModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closePermissionModal() {
        document.getElementById('permissionModal').classList.add('hidden');
        document.getElementById('permissionModal').classList.remove('flex');
    }
</script>