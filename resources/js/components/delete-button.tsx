import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import { Trash } from 'lucide-react';

export default function DeleteButton({ id, featured}: { id: number , featured: string}) {
    const handleDelete = () => {
        const deletes = new Promise((resolve, reject) => {
            router.delete(`/${featured}/${id}`, {
                onSuccess: () => resolve(true),
                onError: () => reject(false),
            });
        });

        toast.promise(deletes, {
            loading: `Deleting ${featured}...`,
            success: `${featured.charAt(0).toUpperCase() + featured.slice(1)} deleted successfully`,
            error: `Failed to delete ${featured}`,
        });
    };

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <Button variant="outline" size="sm" className="hover:bg-red-200 hover:text-red-600"><Trash/></Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This action cannot be undone. This will permanently delete the {featured}.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction asChild>
                        <Button variant="destructive" onClick={handleDelete}>Continue</Button>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
