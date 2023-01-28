
-- View used by postgrest api

create or replace view view_issue as
    select
        slug,
        name,
        birth,
        partial_ids,
        issues,
        occus
    from person
    where jsonb_array_length(issues) != 0
    order by slug;
