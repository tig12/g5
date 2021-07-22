
-- This table contains only one line.
-- Each column contains the information 

create table stats (
    n           integer,
    n_time      integer, -- nb with birth time
    n_day       integer, -- nb with birth day and not time
    countries   jsonb,   -- nb per country
    years       jsonb    -- nb per year
);
