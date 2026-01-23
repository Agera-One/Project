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
import FileDropZone from '@/components/file-drop-zone';
import { dashboard, recently, starred, myDocuments, archives, trash } from '@/routes';
import { Link, router, usePage } from '@inertiajs/react';
import {
  LayoutGrid,
  Clock,
  Star,
  FileText,
  Archive,
  Trash2,
  Upload,
  CheckCircle2,
  AlertCircle,
} from 'lucide-react';
import { useState } from 'react';

const navItems = [
  { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
  { title: 'Recently', href: recently(), icon: Clock },
  { title: 'Starred', href: starred(), icon: Star },
  { title: 'My Documents', href: myDocuments(), icon: FileText },
  { title: 'Archives', href: archives(), icon: Archive },
  { title: 'Trash', href: trash(), icon: Trash2 },
];

export function AppSidebar() {
  const page = usePage();
  const flash = page.props.flash as { success?: string; error?: string } | undefined;
  const [status, setStatus] = useState<'idle' | 'uploading' | 'success' | 'error'>('idle');
  const [progress, setProgress] = useState(0);
  const [message, setMessage] = useState('');

  const handleFilesSelected = (files: File[]) => {
    if (files.length === 0) return;

    setStatus('uploading');
    setProgress(0);
    setMessage('');

    const formData = new FormData();
    files.forEach((file) => {
      formData.append('files[]', file);
    });

    router.post('/documents', formData, {
      forceFormData: true,
      preserveState: true,
      preserveScroll: true,

      onProgress: (progressEvent) => {
        // progressEvent bisa undefined di beberapa kasus → aman dengan ?.
        if (progressEvent?.total && progressEvent?.loaded) {
          const percent = Math.round((progressEvent.loaded / progressEvent.total) * 100);
          setProgress(percent);
        }
      },

      onSuccess: () => {
        setProgress(100);
        setStatus('success');
        setMessage(flash?.success || 'Berhasil mengunggah file');
        setTimeout(() => {
          setStatus('idle');
          setMessage('');
          setProgress(0);
        }, 4000);
      },

      onError: (errors) => {
        setStatus('error');

        const errorMsg =
          (errors as any)?.files ||
          (errors as any)?.['files.0'] ||
          Object.values(errors as any)[0] ||
          'Gagal mengunggah file';

        setMessage(errorMsg);

        setTimeout(() => {
          setStatus('idle');
          setMessage('');
          setProgress(0);
        }, 5000);
      },
    });
  };

  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href={dashboard()}>
                <span className="font-bold text-lg">DocArchive</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <NavMain items={navItems} />
      </SidebarContent>

      <SidebarFooter className="p-4 space-y-4">
        {status !== 'idle' && (
          <div
            className={`
              p-3 rounded-lg text-sm border
              ${status === 'uploading' ? 'bg-blue-950/40 border-blue-700/50' : ''}
              ${status === 'success'   ? 'bg-green-950/40 border-green-700/50' : ''}
              ${status === 'error'     ? 'bg-red-950/40 border-red-700/50' : ''}
            `}
          >
            {status === 'uploading' && (
              <>
                <div className="flex items-center gap-2 mb-2">
                  <Upload className="h-4 w-4 animate-pulse" />
                  <span className="font-medium">Mengunggah...</span>
                </div>
                <div className="h-1.5 bg-gray-700 rounded-full overflow-hidden">
                  <div
                    className="h-full bg-blue-500 transition-all duration-300 ease-out"
                    style={{ width: `${progress}%` }}
                  />
                </div>
                <div className="text-xs text-right mt-1 text-gray-400">{progress}%</div>
              </>
            )}

            {status === 'success' && (
              <div className="flex items-center gap-2 text-green-400">
                <CheckCircle2 className="h-4 w-4" />
                <span>{message}</span>
              </div>
            )}

            {status === 'error' && (
              <div className="flex items-start gap-2 text-red-400">
                <AlertCircle className="h-4 w-4 mt-0.5" />
                <span className="break-words">{message}</span>
              </div>
            )}
          </div>
        )}

        <FileDropZone onFilesSelected={handleFilesSelected} />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
