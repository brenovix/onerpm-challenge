import React from "react";
import { Head } from "@inertiajs/react";
import DataTable from "@/Components/DataTable";
import AudioPlayer from "@/Components/AudioPlayer";
import ExternalURL from "@/Components/ExternalLink";

interface TrackListProps {
  tracks: TrackResponse[];
}

const TrackList: React.FC<TrackListProps> = () => {
  return (
    <>
      <Head title="Track List" />
      <div className="container mx-auto px-4 py-8">
        <DataTable
          endpoint="/api/tracks"
          title="Track List"
          columns={[
            { key: "isrc", label: "ISRC", sortable: false },
            { key: "title", label: "Title", sortable: false },
            { key: "album_title", label: "Album", sortable: false },
            { key: "artists", label: "Artists", sortable: false, render: (artists) => artists.join(", ") },
            { key: "release_date", label: "Release Date", sortable: false, render: (date) => new Date(date).toLocaleDateString(), className: "text-center" },
            { key: "cover", label: "Cover", sortable: false, render: (cover) => <img src={cover} alt="Album Cover" className="w-14 h-14 object-cover" />, className: "text-center" },
            { key: "duration", label: "Duration", sortable: false, render: (duration) => new Date(duration * 1000).toISOString().substring(14, 19), className: "text-center" },
            { key: "br_enabled", label: "Enabled in Brazil", sortable: false, className: "text-center" },
            { key: "preview_url", label: "Preview", sortable: false, render: (previewUrl, track) => <AudioPlayer track={track} />, className: "justify-items-center" },
            { key: "external_url", label: "Track Page", sortable: false, render: (externalUrl) => <div><ExternalURL url={externalUrl} /></div>, className: "justify-items-center" },
          ]}
        />
      </div>
    </>
  )
};

export default TrackList;