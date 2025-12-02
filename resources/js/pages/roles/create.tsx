import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Link, Head, Form } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { BreadcrumbItem } from '@/types';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox';
import { Card } from '@/components/ui/card';

interface Permission {
    id: number;
    name: string;
}

interface Props {
    permissions: Permission[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Create',
        href: '/roles/create',
    },
];

export default function RoleCreatePage({ permissions }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Role" />
            <Card className='m-4'>
                <Form
                    method="post"
                    action="/roles"
                    className="flex flex-col gap-6 p-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Role Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        required
                                        autoFocus
                                        name="name"
                                        placeholder="Enter role name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label>Permissions</Label>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {permissions.map((permission) => (
                                            <label
                                                key={permission.id}
                                                className="flex items-center space-x-3"
                                            >
                                                <Checkbox
                                                    name="permissions[]"
                                                    value={permission.name}
                                                />
                                                <span>{permission.name}</span>
                                            </label>
                                        ))}
                                    </div>
                                    <InputError message={errors.permissions} />
                                </div>

                                <div className="space-x-2">
                                    <Button type="submit" className="mt-2 w-fit">
                                        {processing ? (
                                            <>
                                                <Spinner className="mr-2" />
                                                Creating...
                                            </>
                                        ) : (
                                            'Create Role'
                                        )}
                                    </Button>
                                    <Link href="/roles">
                                        <Button
                                            variant="outline"
                                            type="button"
                                            className="mt-2 w-fit"
                                        >
                                            Back
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </>
                    )}
                </Form>
            </Card>
        </AppLayout>
    );
}