<?php
/*
 * This file is part of the Eko\GoogleTranslateBundle Symfony bundle.
 *
 * (c) Vincent Composieux <vincent.composieux@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eko\GoogleTranslateBundle\Translate\Method;

use Eko\GoogleTranslateBundle\Exception\UnableToDetectException;
use Eko\GoogleTranslateBundle\Translate\Method;
use Eko\GoogleTranslateBundle\Translate\MethodInterface;

/**
 * Class Translator.
 *
 * This is the class to detect language used for a given text
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class Detector extends Method implements MethodInterface
{
    /**
     * Undefined language Google Translate API detector value constant.
     */
    const UNDEFINED_LANGUAGE = 'und';

    /**
     * @var string Google Translate API detector url
     */
    protected $url = 'https://www.googleapis.com/language/translate/v2/detect';

    /**
     * Detect language used for query text given via the Google Translate API.
     *
     * @param string $query A text to detect language
     *
     * @return string
     */
    public function detect($query)
    {
        $options = [
            'key' => $this->apiKey,
            'q'   => $query,
        ];

        return $this->process($options);
    }

    /**
     * Process request and retrieve JSON result.
     *
     * @param array $options
     *
     * @throws UnableToDetectException
     *
     * @return string|null
     */
    protected function process(array $options)
    {
        $result = null;

        $client = $this->getClient();

        $event = $this->startProfiling($this->getName(), $client->getConfig('query'));

        $json = \GuzzleHttp\json_decode($client->get($this->url, ['query' => $options])->getBody()->getContents());

        if (!empty($json->data->translations)) {
            $current = current($json->data->translations);
            $result = $current->translatedText;

            if (self::UNDEFINED_LANGUAGE == $result) {
                throw new UnableToDetectException('Unable to detect language');
            }
        }

        $this->stopProfiling($event, $this->getName(), $result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Detector';
    }
}
