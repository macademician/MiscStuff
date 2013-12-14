-- AppleScript By Matthieu Riegler - http://matthieu.riegler.fr
-- Under Licence CC-0 
-- 
-- This script will kill any Not Responding processes listed in the activity monitor
-- Only tested on OSX Mavericks. 

tell application "Activity Monitor" to run  --We need to run Activity Monitor
tell application "System Events" to tell process "Activity Monitor"
    tell radio button 1 of radio group 1 of group 1 of toolbar 1 of window 1 to click --Using the CPU View 
    tell outline 1 of scroll area 1 of window 1 -- working with the list 
        set notResponding to rows whose value of first static text contains "Not Responding" -- Looking for Not responding processes
        repeat with aProcess in notResponding
            set pid to value of text field 5 of aProcess  -- For each non responding process retrieve the PID 
            if pid is not "" then do shell script ("kill -9 " & pid) -- KILL the PID. 
        end repeat
    end tell
end tell