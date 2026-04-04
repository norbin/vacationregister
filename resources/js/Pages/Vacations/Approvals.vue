<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    requests: Array,
});

const approveForm = useForm({
    status: '',
    manager_comment: '',
});

const confirmingManagerAction = ref(false);
const requestBeingActedUpon = ref(null);
const managerActionType = ref(''); // 'approved' or 'rejected'

const openManagerModal = (request, type) => {
    requestBeingActedUpon.value = request;
    managerActionType.value = type;
    approveForm.status = type;
    approveForm.manager_comment = request.manager_comment || '';
    confirmingManagerAction.value = true;
};

const submitManagerAction = () => {
    approveForm.post(route('vacations.approve', requestBeingActedUpon.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingManagerAction.value = false;
            requestBeingActedUpon.value = null;
        },
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'approved': return 'text-green-600 dark:text-green-400';
        case 'rejected': return 'text-red-600 dark:text-red-400';
        default: return 'text-yellow-600 dark:text-yellow-400';
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <AppLayout title="Vacation Approvals">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Vacation Approvals
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Team Vacation Requests</h3>

                    <div v-if="requests.length === 0" class="text-gray-500 dark:text-gray-400">
                        No vacation requests to review.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Days</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Substitute</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="request in requests" :key="request.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ request.user.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ request.total_days }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ request.substitute?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300 max-w-xs truncate">
                                        {{ request.reason || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span :class="getStatusColor(request.status)">
                                            {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div v-if="request.status === 'pending'" class="flex space-x-2">
                                            <button
                                                @click="openManagerModal(request, 'approved')"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                @click="openManagerModal(request, 'rejected')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                        <div v-else class="text-xs text-gray-400 italic">
                                            {{ request.manager_comment }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manager Action Modal -->
        <DialogModal :show="confirmingManagerAction" @close="confirmingManagerAction = false">
            <template #title>
                {{ managerActionType === 'approved' ? 'Approve' : 'Reject' }} Vacation Request
            </template>

            <template #content>
                <div class="space-y-2">
                    <p class="text-gray-600 dark:text-gray-400">
                        Request from <strong>{{ requestBeingActedUpon?.user.name }}</strong> for <strong>{{ requestBeingActedUpon?.total_days }} days</strong>.
                    </p>
                    <p v-if="requestBeingActedUpon?.substitute" class="text-gray-600 dark:text-gray-400 text-sm">
                        Substitute: <strong>{{ requestBeingActedUpon.substitute.name }}</strong>
                    </p>
                    <p v-if="requestBeingActedUpon?.reason" class="text-sm italic">
                        "{{ requestBeingActedUpon?.reason }}"
                    </p>
                </div>

                <div class="mt-4">
                    <InputLabel for="manager_comment" value="Comment (Optional)" />
                    <textarea
                        id="manager_comment"
                        v-model="approveForm.manager_comment"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        rows="3"
                    ></textarea>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingManagerAction = false">
                    Cancel
                </SecondaryButton>

                <PrimaryButton
                    v-if="managerActionType === 'approved'"
                    class="ms-3"
                    :class="{ 'opacity-25': approveForm.processing }"
                    :disabled="approveForm.processing"
                    @click="submitManagerAction"
                >
                    Approve
                </PrimaryButton>

                <DangerButton
                    v-else
                    class="ms-3"
                    :class="{ 'opacity-25': approveForm.processing }"
                    :disabled="approveForm.processing"
                    @click="submitManagerAction"
                >
                    Reject
                </DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
