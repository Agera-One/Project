import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    dashboard,
    recently,
    starred,
    myDocuments,
    shared,
    archives,
    trash
} from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import {
    LayoutGrid,
    Clock,
    Star,
    FileText,
    Trash2,
    Archive,
    Share2
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import FileDropZone from '@/components/file-drop-zone';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Recently',
        href: recently(),
        icon: Clock,
    },
    {
        title: 'Starred',
        href: starred(),
        icon: Star,
    },
    {
        title: 'My Documents',
        href: myDocuments(),
        icon: FileText,
    },
    {
        title: 'Shared',
        href: shared(),
        icon: Share2,
    },
    {
        title: 'Archives',
        href: archives(),
        icon: Archive,
    },
    {
        title: 'Trash',
        href: trash(),
        icon: Trash2,
    },
];

export function AppSidebar() {
    const handleFiles = (files: File[]) => {
        console.log("File yang diunggah:", files);
        files.forEach(file => {
            console.log(file.name, file.size / 1024 + " KB");
        });
    };
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <FileDropZone onFilesSelected={handleFiles} />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
