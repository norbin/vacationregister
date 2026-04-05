<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]" src="https://laravel.com/assets/img/welcome/background.svg" />
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <img src="/storage/holiday_square.png" class="w-auto" />
                    </div>
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('vacations.calendar')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                        >
                            Calendar
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </header>

                <main class="mt-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <article class="lg:col-span-2 rounded-xl bg-white p-6 text-gray-800 shadow-sm ring-1 ring-black/5 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-white/10">
                            <h2 class="text-2xl font-semibold text-black dark:text-white">
                                Company Vacation Register Application - Functional Overview
                            </h2>

                            <p class="mt-4">
                                The Company Vacation Register is a modern, well-tested application designed to streamline and standardize the management of employee leave within an organization. The system provides a structured workflow for submitting, reviewing, approving, and tracking holiday requests, ensuring transparency and efficiency across all organizational roles.
                            </p>

                            <h3 class="mt-6 text-xl font-semibold text-black dark:text-white">
                                Role-Based Access and Responsibilities
                            </h3>
                            <p class="mt-3">
                                The application implements a clear role-based permission model consisting of <strong>Staff</strong>, <strong>Managers</strong>, and <strong>Administrators</strong>, each with defined capabilities:
                            </p>

                            <h4 class="mt-5 text-lg font-semibold text-black dark:text-white">Staff</h4>
                            <p class="mt-2">
                                Employees can submit vacation requests through an intuitive interface. During the request process, the applicant may optionally designate a colleague who will act as a substitute during their absence. This helps ensure operational continuity while maintaining accountability for delegated responsibilities.
                            </p>
                            <p class="mt-3">Staff members can view:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>Their own vacation balance</li>
                                <li>The status of submitted requests (pending, approved, rejected)</li>
                                <li>The shared company calendar showing colleagues' absences</li>
                            </ul>
                            <p class="mt-3">
                                If a substitute colleague has been assigned, the substitute can update their availability or acknowledge substitution responsibilities even after the request has been submitted or approved.
                            </p>

                            <h4 class="mt-5 text-lg font-semibold text-black dark:text-white">Managers</h4>
                            <p class="mt-2">
                                Managers are responsible for reviewing and approving or rejecting vacation requests submitted by staff members under their supervision. The approval workflow ensures that team capacity remains balanced and business operations are not disrupted by overlapping absences.
                            </p>
                            <p class="mt-3">Managers can:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>View pending vacation requests from their team</li>
                                <li>Approve or reject requests</li>
                                <li>Review substitute assignments</li>
                                <li>Monitor team availability through the calendar interface</li>
                            </ul>
                            <p class="mt-3">
                                The system ensures managers have visibility into potential scheduling conflicts before making approval decisions.
                            </p>

                            <h4 class="mt-5 text-lg font-semibold text-black dark:text-white">Administrators</h4>
                            <p class="mt-2">
                                Administrators have limited system-level privileges. In addition to being able to submit their own leave requests, administrators can manage colleagues roles. This includes modifying the number of available leave days per year based on company policy, employment contracts, or special adjustments.
                            </p>
                            <p class="mt-3">Administrators can:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>Modify users' annual vacation allowances</li>
                                <li>Maintain holiday calendars (national holidays)</li>
                                <li>Oversee system-wide leave usage</li>
                                <li>Access reporting and statistics</li>
                                <li>Perform configuration-level adjustments</li>
                            </ul>

                            <h3 class="mt-6 text-xl font-semibold text-black dark:text-white">
                                Intelligent Leave Calculation
                            </h3>
                            <p class="mt-3">
                                The application automatically calculates the number of vacation days requested by taking into account:
                            </p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>Weekends (non-working days)</li>
                                <li>National holidays configured in the system</li>
                                <li>Partial-day requests (if enabled)</li>
                            </ul>
                            <p class="mt-3">
                                This automation ensures consistency and eliminates manual calculation errors. Employees and managers can immediately see the effective number of leave days deducted from the employee's yearly allowance.
                            </p>

                            <h3 class="mt-6 text-xl font-semibold text-black dark:text-white">
                                Substitute Workflow
                            </h3>
                            <p class="mt-3">
                                When submitting a vacation request, the applicant may optionally nominate a colleague to act as a substitute during the absence period. This supports internal delegation and ensures responsibilities are clearly assigned.
                            </p>
                            <p class="mt-3">Key characteristics of the substitute workflow:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>Substitute selection is optional but encouraged</li>
                                <li>Substitute information remains editable during pending status</li>
                                <li>Substitute details can also be updated after approval if necessary</li>
                                <li>Substitute visibility ensures accountability and transparency</li>
                            </ul>

                            <h3 class="mt-6 text-xl font-semibold text-black dark:text-white">
                                Calendar Visibility
                            </h3>
                            <p class="mt-3">
                                The application includes a comprehensive calendar view that displays all approved vacations across the organization. This shared calendar improves planning and coordination between teams by providing insight into workforce availability.
                            </p>
                            <p class="mt-3">Calendar features include:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>Organization-wide visibility of approved leave</li>
                                <li>Clear visual representation of overlapping absences</li>
                                <li>Filtering options by team or department</li>
                                <li>Real-time updates reflecting newly approved requests</li>
                            </ul>

                            <h3 class="mt-6 text-xl font-semibold text-black dark:text-white">
                                Quality and Reliability
                            </h3>
                            <p class="mt-3">
                                The system is built using modern software engineering practices and includes extensive automated tests to ensure reliability, accuracy, and maintainability. The architecture supports scalability and adaptability to evolving company policies or regulatory requirements.
                            </p>
                            <p class="mt-3">Core quality attributes:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-6">
                                <li>Robust validation logic</li>
                                <li>Consistent business rules enforcement</li>
                                <li>Reliable calculation of leave balances</li>
                                <li>Predictable approval workflows</li>
                                <li>Maintainable modular design</li>
                            </ul>

                            <h3 class="mt-6 text-xl font-semibold text-black dark:text-white">Summary</h3>
                            <p class="mt-3">
                                The Company Vacation Register application provides a comprehensive and efficient solution for managing employee leave. By combining role-based workflows, automatic day calculation, substitute management, and shared calendar visibility, the system ensures a transparent and well-organized vacation planning process. Its modern architecture and thorough test coverage contribute to long-term reliability and ease of maintenance, making it suitable for organizations seeking a dependable leave management platform.
                            </p>
                        </article>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>
