import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { CalendarDays, KeyRound, ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { type SharedData } from '@/types';

interface Props {
    roles: string[];
    permissions: string[];
    member_since: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

export default function MemberDashboard({ roles, permissions, member_since }: Props) {
    const { auth } = usePage<SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-1 flex-col gap-6 p-4">

                {/* Welcome */}
                <div className="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border">
                    <p className="text-muted-foreground text-sm">Welcome back,</p>
                    <h1 className="text-xl font-semibold">{auth.user.name}</h1>
                    <p className="text-muted-foreground text-sm">{auth.user.email}</p>
                </div>

                {/* Info Cards */}
                <div className="grid gap-4 md:grid-cols-3">

                    <InfoCard
                        icon={<CalendarDays className="size-5" />}
                        label="Member Since"
                        content={<p className="text-sm font-medium">{member_since}</p>}
                    />

                    <InfoCard
                        icon={<ShieldCheck className="size-5" />}
                        label="Roles"
                        content={
                            <div className="flex flex-wrap gap-1 pt-1">
                                {roles.map((role, i) => (
                                    <Badge key={i}>{role}</Badge>
                                ))}
                            </div>
                        }
                    />

                    <InfoCard
                        icon={<KeyRound className="size-5" />}
                        label="Permissions"
                        content={
                            <div className="flex flex-wrap gap-1 pt-1">
                                {permissions.length === 0 ? (
                                    <p className="text-muted-foreground text-sm">No permissions assigned.</p>
                                ) : (
                                    permissions.map((permission, i) => (
                                        <Badge key={i} variant="outline">{permission}</Badge>
                                    ))
                                )}
                            </div>
                        }
                    />

                </div>

            </div>
        </AppLayout>
    );
}

function InfoCard({ icon, label, content }: { icon: React.ReactNode; label: string; content: React.ReactNode }) {
    return (
        <div className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div className="mb-3 flex items-center gap-2 text-muted-foreground">
                {icon}
                <p className="text-sm font-medium">{label}</p>
            </div>
            {content}
        </div>
    );
}
