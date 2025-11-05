import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function isSameUrl(
    url1: NonNullable<InertiaLinkProps['href']>,
    url2: NonNullable<InertiaLinkProps['href']>,
) {
    return resolveUrl(url1) === resolveUrl(url2);
}

export function resolveUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}


import { usePage } from "@inertiajs/react";
import { Permission } from '@/types';

type AuthProps = {
    permissions?: Permission[];
};

export default function hasAnyPermission(permissions: string[]) {
    const { auth } = usePage().props as { auth?: AuthProps };
    
    // Handle case when user is not logged in
    if (!auth || !auth.permissions) {
        return false;
    }

    const allPermissions: string[] = auth.permissions.map((p: Permission) => p.name);

    return permissions.some(p => allPermissions.includes(p));
}