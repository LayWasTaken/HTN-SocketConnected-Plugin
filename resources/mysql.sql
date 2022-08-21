-- #!mysql
-- #{players
-- #    {insert
-- #        :xuid string
-- #        :name string
INSERT INTO players (xuid, name)
VALUES (:xuid, :name);
-- #    }
-- #    {init
CREATE TABLE IF NOT EXISTS players (
    xuid VARCHAR(255),
    name VARCHAR(255) NOT NULL,
    discord VARCHAR(255),
    PRIMARY KEY (xuid)
);
-- #    }
-- #    {get
-- #        :xuid string
SELECT discord
FROM players
WHERE xuid = :xuid;
-- #    }
-- #}
-- #{discord_codes 
-- #    {insert
-- #        :xuid string
-- #        :discord string
-- #        :code string
INSERT INTO discord_codes
VALUES (
        :xuid,
        :discord,
        :code
    );
-- #    }
-- #    {init
CREATE TABLE IF NOT EXISTS discord_codes (
    xuid VARCHAR(255),
    discord VARCHAR(255) NOT NULL,
    code varchar(6) NOT NULL,
    PRIMARY KEY (xuid)
);
-- #    }
-- #}