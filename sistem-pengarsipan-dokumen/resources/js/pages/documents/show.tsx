import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { DocumentData } from '@/types/document';

interface Props {
    document: DocumentData;
}

export default function Show({ document }: Props) {
    const isImage = ['jpg', 'jpeg', 'png'].includes(document.extension.toLowerCase());
    const isPdf = document.extension.toLowerCase() === 'pdf';

    return (
    <AppLayout>
            <Head title={document.title} />
            <div className="p-6">
                <h1 className="text-2xl mb-4">{document.title}</h1>
                {isImage && (
                    <img src={document.file_path} alt={document.title} className="max-w-full" />
                )}
                {isPdf && (
                    <iframe src={document.file_path} width="100%" height="600px" />
                )}
                {!isImage && !isPdf && (
                    <p>Preview not available. <a href={document.file_path} download>Download</a></p>
                )}
            </div>
        </AppLayout>
    );
}
