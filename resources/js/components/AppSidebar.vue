<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, BookUser, BriefcaseBusiness, LayoutGrid, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import AppLogo from './AppLogo.vue';

const { props: pageProps } = usePage();
const user = computed(() => pageProps.auth?.user);
const officeScope = computed(() => user.value?.office_scope || user.value?.employee?.assignment_name || 'No office assigned');

const mainNavItems = computed<NavItem[]>(() => {
    const isAdmin = user.value?.is_admin ?? 0;

    if (![1, 2].includes(isAdmin)) {
        return [{ title: 'Profile', href: route('profile'), icon: BookOpen }];
    }

    return [
        { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
        { title: 'Employee', href: route('employee.index'), icon: BookUser },
        { title: 'Positions', href: route('positions.index'), icon: BriefcaseBusiness },
        {
            title: 'Users',
            href: route(isAdmin === 1 ? 'users.index' : 'users_admin.index'),
            icon: Users,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-gradient-to-b from-gray-700 to-gray-900">
        <SidebarHeader class="bg-gradient-to-b from-gray-700 to-gray-900">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="text-white">
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-gradient-to-b from-gray-700 to-gray-900">
            <div v-if="user?.is_admin === 2" class="mx-3 mt-2 rounded-md bg-white/10 px-3 py-2 text-xs text-white">
                <span class="block text-white/70">Office scope</span>
                <span class="font-medium">{{ officeScope }}</span>
            </div>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="bg-gradient-to-b from-gray-700 to-gray-900">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
