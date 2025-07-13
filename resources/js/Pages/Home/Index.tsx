import React from "react";
import { Head } from "@inertiajs/react";
import DataTable from "@/Components/DataTable";
import AudioPlayer from "@/Components/AudioPlayer";

interface TrackListProps {
  tracks: Track[];
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
            { key: "album.title", label: "Album", sortable: false },
            { key: "artists", label: "Artists", sortable: false },
            { key: "album.releaseDate", label: "Release Date", sortable: false },
            { key: "album.cover", label: "Cover", sortable: false, render: (cover) => <img src={cover} alt="Album Cover" className="w-12 h-12 object-cover" /> },
            { key: "duration", label: "Duration", sortable: false, render: (duration) => new Date(duration * 1000).toISOString().substring(14, 5) },
            { key: "brEnabled", label: "Enabled in Brazil", sortable: false },
            { key: "previewUrl", label: "Preview", sortable: false, render: async (track) => AudioPlayer({ track }) },
            { key: "externalUrl", label: "Track Page", sortable: false },
          ]}
        />
      </div>
    </>
  )
};

export default TrackList;