
create table issue_wikiproject (
    id_issue        int not null references issue(id),
    id_wikiproject  int not null references wikiproject(id),
    primary key(id_issue, id_wikiproject)
);
-- to find the issues of a wikiproject
create index issue_wikiproject_id_issue_idx on issue_wikiproject(id_issue);
-- to find the wikiproject related to an issue
create index issue_wikiproject_id_wikiproject_idx on issue_wikiproject(id_wikiproject);
