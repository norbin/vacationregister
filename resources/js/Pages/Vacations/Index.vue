<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import DialogModal from '@/Components/DialogModal.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    myRequests: Array,
    vacationDaysRemaining: Number,
    vacationDaysYearly: Number,
    availableSubstitutes: Array,
});

const form = useForm({
    start_date: '',
    end_date: '',
    reason: '',
    substitute_id: '',
});

const approveForm = useForm({
    status: '',
    manager_comment: '',
});

const updateSubstituteForm = useForm({
    substitute_id: '',
});

const confirmingRequestCancellation = ref(false);
const requestIdBeingCancelled = ref(null);

const editingSubstitute = ref(false);
const requestBeingEdited = ref(null);

const submitRequest = () => {
    form.post(route('vacations.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const confirmRequestCancellation = (id) => {
    requestIdBeingCancelled.value = id;
    confirmingRequestCancellation.value = true;
};

const cancelRequest = () => {
    form.delete(route('vacations.destroy', requestIdBeingCancelled.value), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingRequestCancellation.value = false;
            requestIdBeingCancelled.value = null;
        },
    });
};

const editSubstitute = (request) => {
    requestBeingEdited.value = request;
    updateSubstituteForm.substitute_id = request.substitute_id || '';
    editingSubstitute.value = true;
};

const updateSubstitute = () => {
    updateSubstituteForm.put(route('vacations.update', requestBeingEdited.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingSubstitute.value = false;
            requestBeingEdited.value = null;
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
    <AppLayout title="Vacations">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Vacation Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Vacation Days Remaining</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ vacationDaysRemaining }} / {{ vacationDaysYearly }}</div>
                    </div>
                </div>

                <!-- Request Form -->
                <FormSection @submitted="submitRequest">
                    <template #title>
                        Request Vacation
                    </template>

                    <template #description>
                        Submit a new vacation request for approval.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="start_date" value="Start Date" />
                            <TextInput
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.start_date" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="end_date" value="End Date" />
                            <TextInput
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.end_date" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="substitute_id" value="Substitute (Optional)" />
                            <select
                                id="substitute_id"
                                v-model="form.substitute_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            >
                                <option value="">Select a substitute</option>
                                <option v-for="user in availableSubstitutes" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.substitute_id" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="reason" value="Reason (Optional)" />
                            <textarea
                                id="reason"
                                v-model="form.reason"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.reason" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <ActionMessage :on="form.recentlySuccessful" class="me-3">
                            Submitted.
                        </ActionMessage>

                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Submit Request
                        </PrimaryButton>
                    </template>
                </FormSection>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">My Vacation Requests</h3>

                    <div v-if="myRequests.length === 0" class="text-gray-500 dark:text-gray-400">
                        No requests found.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Days</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Substitute</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="request in myRequests" :key="request.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ request.total_days }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ request.substitute?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span :class="getStatusColor(request.status)">
                                            {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-3">
                                            <button
                                                v-if="['pending', 'approved'].includes(request.status)"
                                                @click="editSubstitute(request)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                Edit Substitute
                                            </button>

                                            <button
                                                v-if="request.status === 'pending'"
                                                @click="confirmRequestCancellation(request.id)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation Confirmation Modal -->
        <DialogModal :show="confirmingRequestCancellation" @close="confirmingRequestCancellation = false">
            <template #title>
                Cancel Vacation Request
            </template>

            <template #content>
                Are you sure you want to cancel this vacation request?
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingRequestCancellation = false">
                    Back
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="cancelRequest"
                >
                    Cancel Request
                </DangerButton>
            </template>
        </DialogModal>

        <!-- Edit Substitute Modal -->
        <DialogModal :show="editingSubstitute" @close="editingSubstitute = false">
            <template #title>
                Edit Substitute
            </template>

            <template #content>
                <div v-if="requestBeingEdited">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Update the substitute person for your vacation request from
                        <strong>{{ formatDate(requestBeingEdited.start_date) }}</strong> to
                        <strong>{{ formatDate(requestBeingEdited.end_date) }}</strong>.
                    </p>

                    <div>
                        <InputLabel for="edit_substitute_id" value="Substitute (Optional)" />
                        <select
                            id="edit_substitute_id"
                            v-model="updateSubstituteForm.substitute_id"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="">Select a substitute</option>
                            <option v-for="user in availableSubstitutes" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                        <InputError :message="updateSubstituteForm.errors.substitute_id" class="mt-2" />
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="editingSubstitute = false">
                    Cancel
                </SecondaryButton>

                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': updateSubstituteForm.processing }"
                    :disabled="updateSubstituteForm.processing"
                    @click="updateSubstitute"
                >
                    Update Substitute
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
