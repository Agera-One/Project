# Document Archiver - Mobile PWA

A beautiful, responsive Progressive Web App for document archiving and management built with Next.js, React, and Tailwind CSS.

## Features

- **Mobile-First Design**: Optimized for mobile devices with responsive layout
- **Progressive Web App**: Works offline with service worker support
- **Dark Theme**: Eye-friendly dark interface with cyan accents
- **Dashboard**: View document statistics and storage usage at a glance
- **Document Management**: Browse, star, archive, and delete documents
- **Search Functionality**: Find documents quickly with the search feature
- **File Type Visualization**: See storage breakdown by file type
- **Responsive Navigation**: Desktop sidebar + mobile bottom navigation
- **Installable**: Add to home screen on mobile and desktop

## Getting Started

### Prerequisites
- Node.js 18+ 
- npm or yarn

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd document-archiver
```

2. Install dependencies:
```bash
npm install
```

3. Run the development server:
```bash
npm run dev
```

4. Open [http://localhost:3000](http://localhost:3000) in your browser

## Installation as PWA

### On Mobile (iOS/Android)
1. Open the app in your mobile browser
2. Tap the share button (iOS) or menu button (Android)
3. Select "Add to Home Screen" or "Install App"
4. The app will be installed as a native-like app

### On Desktop
1. Visit the app URL in Chrome or Edge
2. Click the "Install" button in the address bar (or menu)
3. The app will be installed as a desktop app

## Project Structure

```
├── app/
│   ├── page.tsx              # Main app page
│   ├── layout.tsx            # Root layout with PWA config
│   └── globals.css           # Global styles and theme
├── components/
│   ├── Header.tsx            # Top navigation header
│   ├── Navigation.tsx        # Desktop/mobile sidebar
│   ├── BottomNav.tsx         # Mobile bottom navigation
│   ├── Dashboard.tsx         # Dashboard screen
│   ├── DocumentsList.tsx     # Documents list view
│   ├── DocumentCard.tsx      # Individual document card
│   ├── StatCard.tsx          # Statistics card
│   ├── StorageChart.tsx      # Donut chart for storage
│   └── FileTypeCard.tsx      # File type breakdown card
├── public/
│   ├── manifest.json         # PWA manifest
│   ├── sw.js                 # Service worker
│   └── icon-*.png            # App icons
└── package.json
```

## Theme

The app uses a dark theme with cyan accents (#06b6d4) and is fully customizable through `globals.css`.

### Color Palette
- **Background**: #0f172a (Dark slate)
- **Card**: #1a2845 (Dark blue)
- **Primary/Accent**: #06b6d4 (Cyan)
- **Text**: #f1f5f9 (Light slate)
- **Borders**: #1e293b (Medium dark)

## Key Technologies

- **Framework**: Next.js 16
- **Styling**: Tailwind CSS v4
- **UI Components**: shadcn/ui
- **Icons**: Lucide React
- **PWA Features**: Service Worker, Web App Manifest

## Customization

### Adding Documents
Edit the mock data in `/components/DocumentsList.tsx` to add real documents or connect to a backend API.

### Changing Colors
Update the CSS custom properties in `/app/globals.css` to customize the color scheme.

### Updating App Metadata
Edit `/app/layout.tsx` metadata and `/public/manifest.json` to customize the app name, description, and icons.

## Offline Support

The app includes a service worker that caches essential files and enables offline functionality. Users can browse previously loaded content even without an internet connection.

## Performance

- Optimized for mobile with minimal bundle size
- Service worker caching for fast page loads
- Responsive images and lazy loading
- Efficient CSS with Tailwind CSS
- Optimized React components with proper memoization

## Browser Support

- Chrome/Edge 88+
- Safari 14+ (iOS 14+)
- Firefox 85+
- Samsung Internet 14+

## Deployment

### Deploy to Vercel
```bash
npm run build
```

Then connect your GitHub repository to Vercel for automatic deployments.

### Deploy to Other Platforms
```bash
npm run build
npm run start
```

## Future Enhancements

- Backend integration for real document storage
- User authentication
- Cloud sync across devices
- Document preview and annotation
- Advanced search with filters
- Dark/light theme toggle
- Multi-language support
- Real-time notifications

## License

MIT

## Support

For issues or questions, please create an issue in the repository.
