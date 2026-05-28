import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type Role, type User } from '@/types';
import { Head } from '@inertiajs/react';
import { ShieldCheck, Users, KeyRound } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Props {
    total_users: number;
    total_roles: number;
    total_permissions: number;
    recent_users: User[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

export default function AdminDashboard({ total_users, total_roles, total_permissions, recent_users }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-1 flex-col gap-6 p-4">

                {/* Stat Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard icon={<Users className="size-5" />} label="Total Users" value={total_users} />
                    <StatCard icon={<ShieldCheck className="size-5" />} label="Total Roles" value={total_roles} />
                    <StatCard icon={<KeyRound className="size-5" />} label="Total Permissions" value={total_permissions} />
                </div>

                {/* Recent Users */}
                <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <h2 className="text-sm font-semibold">Recent Users</h2>
                    </div>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Joined</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {recent_users.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={4} className="text-center text-muted-foreground">
                                        No users yet.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                recent_users.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell>{user.name}</TableCell>
                                        <TableCell>{user.email}</TableCell>
                                        <TableCell>
                                            {Array.isArray(user.roles) && (user.roles as Role[]).map((role, i) => (
                                                <Badge key={i} className="mr-1">{role.name}</Badge>
                                            ))}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground text-sm">
                                            {new Date(user.created_at).toLocaleDateString()}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

            </div>
        </AppLayout>
    );
}

function StatCard({ icon, label, value }: { icon: React.ReactNode; label: string; value: number }) {
    return (
        <div className="flex items-center gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <div className="flex size-10 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                {icon}
            </div>
            <div>
                <p className="text-sm text-muted-foreground">{label}</p>
                <p className="text-2xl font-semibold">{value}</p>
            </div>
        </div>
    );
}
