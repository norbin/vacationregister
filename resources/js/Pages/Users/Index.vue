<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    users: Array,
});

const editingUser = useForm({
    id: null,
    vacation_days_yearly: 0,
    is_admin: false,
    is_manager: false,
});

const selectUser = (user) => {
    editingUser.id = user.id;
    editingUser.vacation_days_yearly = user.vacation_days_yearly;
    editingUser.is_admin = user.is_admin;
    editingUser.is_manager = user.is_manager;
};

const updateUser = () => {
    editingUser.put(route('users.update', editingUser.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingUser.id = null;
        },
    });
};
</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex flex-col">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Name
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Email
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Teams
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Pending
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Annual
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Used
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Remaining
                                                </th>
                                                <th scope="col" class="relative px-6 py-3">
                                                    <span class="sr-only">Edit</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="user in users" :key="user.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ user.name }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">
                                                        {{ user.email }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center space-x-4">
                                                        <label class="flex items-center">
                                                            <Checkbox
                                                                :checked="editingUser.id === user.id ? editingUser.is_admin : user.is_admin"
                                                                @update:checked="editingUser.is_admin = $event"
                                                                :disabled="editingUser.id !== user.id || editingUser.processing"
                                                            />
                                                            <span class="ms-2 text-sm text-gray-600" :class="{ 'opacity-50': editingUser.id !== user.id }">Admin</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <Checkbox
                                                                :checked="editingUser.id === user.id ? editingUser.is_manager : user.is_manager"
                                                                @update:checked="editingUser.is_manager = $event"
                                                                :disabled="editingUser.id !== user.id || editingUser.processing"
                                                            />
                                                            <span class="ms-2 text-sm text-gray-600" :class="{ 'opacity-50': editingUser.id !== user.id }">Manager</span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span v-if="user.has_pending_requests" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Yes
                                                    </span>
                                                    <span v-else class="text-gray-400 text-xs italic">
                                                        None
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <template v-if="editingUser.id === user.id">
                                                        <div class="flex items-center">
                                                            <input
                                                                type="number"
                                                                class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                v-model="editingUser.vacation_days_yearly"
                                                                min="0"
                                                                max="365"
                                                            />
                                                            <InputError :message="editingUser.errors.vacation_days_yearly" class="ml-2" />
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        {{ user.vacation_days_yearly }}
                                                    </template>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    {{ user.vacation_days_used }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    <span :class="{'text-red-600 font-bold': user.vacation_days_remaining < 0}">
                                                        {{ user.vacation_days_remaining }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <template v-if="editingUser.id === user.id">
                                                        <PrimaryButton @click="updateUser" :class="{ 'opacity-25': editingUser.processing }" :disabled="editingUser.processing">
                                                            Save
                                                        </PrimaryButton>
                                                        <button @click="editingUser.id = null" class="ml-2 text-gray-600 hover:text-gray-900">
                                                            Cancel
                                                        </button>
                                                    </template>
                                                    <template v-else>
                                                        <button @click="selectUser(user)" class="text-indigo-600 hover:text-indigo-900">
                                                            Edit
                                                        </button>
                                                    </template>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
