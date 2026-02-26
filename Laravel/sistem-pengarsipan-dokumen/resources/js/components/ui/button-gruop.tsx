import { useState } from "react";
import { List, Grid } from "lucide-react";

export default function ButtonGroup() {
    const [view, setView] = useState("list");

    return (
        <div className="flex border-2 border-sidebar-accent rounded-lg w-fit">
            <button
                onClick={() => setView("list")}
                className={`p-2 rounded-sm transition ${view === "list"
                        ? "bg-sidebar-accent text-white"
                        : "text-zinc-400 hover:text-white"
                    }`}
            >
                <List size={20} />
            </button>

            <button
                onClick={() => setView("grid")}
                className={`p-2 rounded-sm transition ${view === "grid"
                        ? "bg-sidebar-accent text-white"
                        : "text-zinc-400 hover:text-white"
                    }`}
            >
                <Grid size={20} />
            </button>
        </div>
    );
}
