type TrackResponse = {
  id: number;
  isrc: string;
  title: string;
  album_title: string;
  artists: string;
  release_date: string;
  cover: string;
  duration: number;
  br_enabled: boolean;
  preview_url: string | null;
  external_url: string;
}
