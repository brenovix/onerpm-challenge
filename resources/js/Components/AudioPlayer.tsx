import { Pause, Play } from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

type PlayerProps = {
  track?: Track
};

const AudioPlayer: React.FC<PlayerProps> = ({ track }) => {
  const [isPlaying, setIsPlaying] = useState(false);

  const audioRef = useRef<HTMLAudioElement | null>(null);

  const isDisabled = !track || !track.previewUrl;

  useEffect(() => {
    if (track && track.previewUrl) {
      if (!audioRef.current) {
        audioRef.current = new Audio(track.previewUrl);
      }
      if (isPlaying) {
        audioRef.current.pause();
        setIsPlaying(false);
      }

      const handleAudioEnded = () => {
        setIsPlaying(false);
      };
  
      audioRef.current?.addEventListener("ended", handleAudioEnded);

      return () => {
        audioRef.current?.pause();
        audioRef.current?.removeEventListener("ended", handleAudioEnded);
      }
    }
    audioRef.current = null;
    setIsPlaying(false);
  }, [track]);

  const handleClick = () => {
    if (isDisabled || !audioRef.current) return;

    if (isPlaying) {
      audioRef.current.pause();
      setIsPlaying(false);
    } else {
      audioRef.current.play();
      setIsPlaying(true);
    }
  };
  
  return (
    <button className={`flex items-center justify-center w-10 h-10 rounded-full p-0 transition-all duration-300
      ease-in-out shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-opacity-75
      ${isDisabled 
        ? 'bg-gray-700 text-gray-500 cursor-not-allowed opacity-70' 
        : 'bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-400'}`} 
      aria-label={isPlaying ? 'Pause Track' : 'Play Track'} onClick={handleClick} disabled={isDisabled}>
        {isPlaying ? (
        <Pause className="w-8 h-8" />
      ) : (
        <Play className="w-8 h-8" />
      )}
    </button>
  )
};

export default AudioPlayer;