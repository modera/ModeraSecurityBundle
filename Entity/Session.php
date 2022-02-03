<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="modera_security_session",
 *     options={"collate":"utf8mb4_bin", "charset":"utf8mb4"},
 *     indexes={@ORM\Index(name="sess_lifetime_idx", columns={"sess_lifetime"})}
 * )
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2015 Modera Foundation
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(name="sess_id", type="binary", length=128, nullable=false)
     */
    protected ?string $id = null;

    /**
     * @ORM\Column(name="sess_data", type="blob", length=65532, nullable=false)
     */
    protected ?string $data = null;

    /**
     * @ORM\Column(name="sess_time", type="integer", nullable=false, options={"unsigned"=true})
     */
    protected ?int $time = null;

    /**
     * @ORM\Column(name="sess_lifetime", type="integer", nullable=false, options={"unsigned"=true})
     */
    protected ?int $lifetime = null;
}
