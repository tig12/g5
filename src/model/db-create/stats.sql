
-- This table contains only one line.
-- Each column contains the information 

create table stats (
    n           integer,
    n_time      integer, -- nb with birth time
    n_notime    integer, -- nb without birth time (but with birth day)
    n_issues    integer, -- nb rows in view view_issue
    n_checked   integer default 0, -- nb with trust != 5
    countries   jsonb,   -- nb per country
    years       jsonb    -- nb per year
);
